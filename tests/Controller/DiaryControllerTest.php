<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;

class DiaryControllerTest extends WebTestCase
{
    private KernelBrowser|null $client = null;

    public function setUp() : void
    {
        $this->client = static::createClient();
        $this->userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->userRepository->findOneByEmail('halit.cinici@live.fr');
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
        $this->client->loginUser($this->user);
    }

    public function testHomepageIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function  testHomepage()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));
        $this->assertSame(1, $crawler->filter('html:contains("Bienvenue sur FoodDiarry!")')->count());
    }

    public function testAddRecord()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('add-new-record'));
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['food[entitled]'] = 'Plat de pâtes';
        $form['food[calories]'] = 600;
        $this->client->submit($form);
        $this->client->followRedirect();
        echo $this->client->getResponse()->getContent();
        $this->assertSelectorTextContains('div.alert.alert-success','Une nouvelle entrée dans votre journal a bien été ajoutée');
    }

    public function testAddRecordOut()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('add-new-record'));
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['food[entitled]'] = ' ';
        $form['food[calories]'] = 60;
        $this->client->submit($form);
        echo $this->client->getResponse()->getContent();
        $this->assertSelectorTextContains('h1', 'Ajouter un repas');
    }

    public function testList()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));
        $link = $crawler->selectLink('Voir tous les rapports')->link();
        $crawler = $this->client->click($link);
        $info = $crawler->filter('h1')->text();
            // On retire les retours à la ligne pour faciliter la vérification
        $info = $string = trim(preg_replace('/\s\s+/', ' ', $info));
        $this->assertSame("Tous les rapports Tout ce qui a été mangé !", $info);
    }

    
}

