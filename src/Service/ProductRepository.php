<?php


namespace App\Service;


use App\Exception\InvalidSortByException;
use App\Exception\InvalidSortOrderException;
use App\Exception\NoProductException;
use App\Exception\ProductNotFoundException;

class ProductRepository
{
    CONST SORT_ASC = "asc";
    CONST SORT_DESC = "desc";

    private $rawData;
    private $products = [];
    private $category = null;
    private $colorIndex = [];
    private $brandIndex = [];

    /**
     * ProductRepository constructor.
     * @throws InvalidSortOrderException
     * @throws NoProductException
     */

    public function __construct()
    {
        $this->fetchData();
        $this->prepareProducts();
        $this->prepareCategory();
        $this->prepareIndexes();
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param $sku
     * @return array
     * @throws ProductNotFoundException
     */
    public function getProduct($sku){
        if(!isset( $this->products[$sku])){
            throw new ProductNotFoundException();
        }
        return $this->products[$sku];
    }

    /**
     * @return array
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function getColors(){
        return array_keys($this->colorIndex);
    }
    public function getBrands(){
        return array_keys($this->brandIndex);
    }

    public function getSkusByColor($color){
         return $this->colorIndex[$color];
    }
    public function getSkusByBrand($brand){
         return $this->brandIndex[$brand];
    }

    /**
     * @param $skus
     * @param null $products
     * @return array
     */
    public function getProductsBySku($skus, $products = null)
    {
        $filteredProducts = [];
        if(null===$products)
        {
            $products = $this->products;
        }
        foreach ($skus as $sku) {
            if (isset($products[$sku])) {
                $filteredProducts[$sku] = $products[$sku];
            }
        }
        return $filteredProducts;
    }

    /**
     * @param $products
     * @param string $sortBy
     * @param string $sortOrder
     * @return array
     * @throws InvalidSortOrderException
     */
    public function getSortedProductsBy($products, $sortBy, $sortOrder = ProductRepository::SORT_ASC)
    {
        if (!in_array($sortOrder, [ProductRepository::SORT_ASC, ProductRepository::SORT_DESC])) {
            throw new InvalidSortOrderException();
        }

        if ($sortOrder == ProductRepository::SORT_ASC) {
            $order = 1;
        } else {
            $order = -1;
        }

        usort($products, function ($a, $b) use ($sortBy, $order) {
            if (!isset($a[$sortBy])) {
                throw new InvalidSortByException();
            }

            if ($a[$sortBy] > $b[$sortBy]) {
                return $order;
            } else {
                return -1 * $order;
            }
        });

        return $products;
    }

    private function fetchData()
    {
        $this->rawData = json_decode(file_get_contents("../assets/products.json"), true);
    }

    private function prepareProducts()
    {
        foreach ($this->rawData['products'] as $product) {
            $this->products[$product['sku']] = $product;
        }
    }
    private function prepareCategory()
    {
        $this->category = $this->rawData['category'];
    }

    /**
     * @throws NoProductException
     */

    private function prepareIndexes()
    {
        if (null === $this->products) {
            throw new NoProductException();
        }
        foreach ($this->products as $product) {
            $this->colorIndex[$product['color']][] = $product['sku'];
            $this->brandIndex[$product['brand']][] = $product['sku'];
        }
    }
}
