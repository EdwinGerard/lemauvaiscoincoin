<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Department;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $category = $manager->getRepository(Category::class)->findOneByName('Adulte');
        $department = $manager->getRepository(Department::class)->findOneByName('Loiret');
        $creator = $manager->getRepository(User::class)->findOneByUserName('DuckAdmin');
        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName('Produit'.$i);
            $product->setCategory($category);
            $product->setDepartment($department);
            $product->setCreator($creator);
            $product->setPicture('7ea9c4992524a7ef59aceba107a3400f.jpeg');
            $product->setDescription('Une petite description');
            $product->setPrice($i*10);
            $manager->persist($product);

        }

        $manager->flush();
    }
}
