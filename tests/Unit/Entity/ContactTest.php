<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Contact;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $contact = new Contact();

        $contact->setIdentity('John Doe');
        self::assertSame('John Doe', $contact->getIdentity());

        $contact->setEmail('john@example.com');
        self::assertSame('john@example.com', $contact->getEmail());

        $contact->setContent('Hello');
        self::assertSame('Hello', $contact->getContent());

        self::assertNull($contact->getId());
    }

    public function testFieldsAreNullByDefault(): void
    {
        $contact = new Contact();

        self::assertNull($contact->getIdentity());
        self::assertNull($contact->getEmail());
        self::assertNull($contact->getContent());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $contact = new Contact();

        self::assertInstanceOf(Contact::class, $contact->setIdentity('John'));
        self::assertInstanceOf(Contact::class, $contact->setEmail('john@example.com'));
        self::assertInstanceOf(Contact::class, $contact->setContent('Message'));
    }
}
