<?php

namespace App\Tests;

use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $this->logIn();

        $this->client->request('GET', $url);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

    }

    public function urlProvider()
    {
        $customerId = 174;
        $machineId = 1;
        $orderId = 1;
        $ticketId = 108;
        $serviceContractId = 1;

        yield ["/login"];
        yield ["/password-recovery"];

        yield ["/admin/currencies"];
        yield ["/admin/currencies/edit/0"];
        yield ["/admin/system-settings"];
        // removed other links
        yield ["/user-groups"];
        yield ["/user-groups-access-settings"];
        yield ["/user-groups/edit/0"];
        yield ["/users"];
        yield ["/users/edit/0"];

    }


    private function logIn()
    {

        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';

        //
        $user = new User();
        $user
            ->setId(1250)
            ->setEmail("< hidden >")
            ->setStatus(User::STATUS_ACTIVE)
            ->setEncodedPassword('< hidden >')
            ->setLang("en");

        $token = new UsernamePasswordToken(
            $user,
            //"maratkagyu@gmail.com",
            null,
            $firewall,
            ['ROLE_USER']
        );


        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}