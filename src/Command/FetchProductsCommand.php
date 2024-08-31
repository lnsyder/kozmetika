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
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(
    name: 'app:fetch-products',
    description: 'Fetch products from an external web page and save them to the database.'
)]
class FetchProductsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private Client $client;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->client = new Client();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $page = 0;
        $hasMorePages = true;

        while ($hasMorePages) {
            $url = 'https://www.gratis.com/makyaj-c-501?page=' . $page;
            $io->note("Fetching page: $page");

            $response = $this->client->request('GET', $url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // Check if there are any product cards on the current page
            $productCards = $crawler->filter('.product-cards');
            if ($productCards->count() === 0) {
                $hasMorePages = false;
                continue;
            }

            // Process each product card
            $productCards->each(function (Crawler $node) {
                $name = $node->filter('.title')->text();
                $description = null;
                $price = $node->filter('.amount')->text();

                $product = new Product();
                $product->setName($name);
                $product->setDescription($description);
                $product->setPrice($price);

                $this->entityManager->persist($product);
            });

            $this->entityManager->flush();
            $page++;
        }

        $io->success('Ürünler başarıyla veritabanına kaydedildi.');

        return Command::SUCCESS;
    }
}