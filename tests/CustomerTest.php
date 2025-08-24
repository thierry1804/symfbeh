<?php

namespace App\Tests;

use App\Entity\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testCustomerCreation(): void
    {
        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setLastName('Doe');
        $customer->setEmail('john.doe@example.com');
        $customer->setStatus('prospect');

        $this->assertEquals('John', $customer->getFirstName());
        $this->assertEquals('Doe', $customer->getLastName());
        $this->assertEquals('john.doe@example.com', $customer->getEmail());
        $this->assertEquals('prospect', $customer->getStatus());
        $this->assertEquals('John Doe', $customer->getFullName());
    }

    public function testCustomerStatusLabel(): void
    {
        $customer = new Customer();
        
        $customer->setStatus('prospect');
        $this->assertEquals('Prospect', $customer->getStatusLabel());
        
        $customer->setStatus('client');
        $this->assertEquals('Client', $customer->getStatusLabel());
        
        $customer->setStatus('vip');
        $this->assertEquals('VIP', $customer->getStatusLabel());
        
        $customer->setStatus('inactif');
        $this->assertEquals('Inactif', $customer->getStatusLabel());
    }

    public function testCustomerStatusColor(): void
    {
        $customer = new Customer();
        
        $customer->setStatus('prospect');
        $this->assertEquals('warning', $customer->getStatusColor());
        
        $customer->setStatus('client');
        $this->assertEquals('success', $customer->getStatusColor());
        
        $customer->setStatus('vip');
        $this->assertEquals('danger', $customer->getStatusColor());
        
        $customer->setStatus('inactif');
        $this->assertEquals('secondary', $customer->getStatusColor());
    }

    public function testCustomerTags(): void
    {
        $customer = new Customer();
        
        $this->assertEmpty($customer->getTagsArray());
        
        $customer->addTag('important');
        $customer->addTag('vip');
        
        $this->assertContains('important', $customer->getTagsArray());
        $this->assertContains('vip', $customer->getTagsArray());
        $this->assertCount(2, $customer->getTagsArray());
        
        $customer->removeTag('important');
        $this->assertNotContains('important', $customer->getTagsArray());
        $this->assertContains('vip', $customer->getTagsArray());
        $this->assertCount(1, $customer->getTagsArray());
    }
}
