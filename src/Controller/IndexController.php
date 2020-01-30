<?php

namespace App\Controller;

use App\Service\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(ProductRepository $productRepository, Request $request)
    {

        return $this->redirectToRoute('filter');

    }

    /**
     * @Route("/filter/color/{color}/brand/{brand}/{sort}", name="filter")
     */
    public function filter(ProductRepository $productRepository, Request $request, $color = 'all', $brand = 'all', $sort = null)
    {
        if ($color != "all") {
            $sku = $productRepository->getSkusByColor($color);
            $products = $productRepository->getProductsBySku($sku);
        } else {
            $products = $productRepository->getProducts();
        }

        if ($brand != "all") {
            $sku = $productRepository->getSkusByBrand($brand);
            $products = $productRepository->getProductsBySku($sku,$products);
        }

        if (null !== $sort) {
            $products = $productRepository->getSortedProductsBy($products, $sort);
        }
        return $this->render('index/index.html.twig',
            [  'colors' => $productRepository->getColors(),
                'brands' => $productRepository->getBrands(),
                'selectedColor' => $color,
                'selectedBrand' => $brand,
                'selectedSort' => $sort,
                'products' => $products,
                'category' => $productRepository->getCategory(),
               ]);
    }

    /**
     * @Route("/show/{sku}", name="show")
     */
    public function show(ProductRepository $productRepository, Request $request, $sku)
    {
        return $this->render('index/show.html.twig', [
                'data' => $productRepository->getProduct($sku),
                'colors' => $productRepository->getColors(),
                'brands' => $productRepository->getBrands(),
                'selectedColor' => 'all',
                'selectedBrand' => 'all',
                'selectedSort' => 'price',
                'category' => $productRepository->getCategory(),

            ]);

    }

}
