<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndexActionA()
    {
        $client = static::createClient();
        
        $client->request('GET', '/');
        
        $this->assertTrue($client->getResponse()->isRedirect());
    }
}