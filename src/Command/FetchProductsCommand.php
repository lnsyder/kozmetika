<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sunra\PhpSimple\HtmlDomParser;

#[AsCommand(
    name: 'app:fetch-products',
    description: 'Fetch products from an external web page and save them to the database.'
)]
class FetchProductsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = new Client();

        $response = $client->request('GET', 'https://www.gratis.com/makyaj-c-501');
        $html = (string) $response->getBody();

        $dom = HtmlDomParser::str_get_html($html);

        // Ürünleri seçmek için uygun CSS seçicilerini kullanın
        foreach ($dom->find('.product-item') as $productElement) {
            $name = $productElement->find('.product-title', 0)->plaintext;
            $description = $productElement->find('.product-description', 0)->plaintext;
            $price = $productElement->find('.product-price', 0)->plaintext;

            $product = new Product();
            $product->setName($name);
            $product->setDescription($description);
            $product->setPrice($price);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        $io->success('Ürünler başarıyla veritabanına kaydedildi.');

        return Command::SUCCESS;
    }
}