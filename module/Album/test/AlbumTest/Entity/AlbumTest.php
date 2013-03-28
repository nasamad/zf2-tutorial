<?php

namespace AlbumTest\Entity;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceManager;
use AlbumTest\Bootstrap;
use Album\Entity\Album;
use PHPUnit_Framework_TestCase;

class AlbumTest extends PHPUnit_Framework_TestCase
{
    /**
     * Service Manager
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;

    /**
     * Doctrine Entity Manager
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;


    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');

        parent::setUp();
    }


    public function testCanInsertNewRecord()
    {
        $data = array(
            'artist' => 'Led Zeppelin',
            'title' => 'Led Zeppelin III',
            'discs' => 1
        );
        $album = new Album;
        $album->populate($data);
        // save data
        $this->em->persist($album);
        $this->em->flush();

        $this->assertEquals($data['artist'], $album->artist);
        $this->assertEquals($data['title'], $album->title);

        return $album->id;
    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanUpdateInsertedRecord($id)
    {
        $data = array(
            'id' => $id,
            'artist' => 'Led Zeppelin',
            'title' => 'Led Zeppelin I',
            'discs' => 1
        );
        $album = $this->em->getRepository('Album\Entity\Album')->find($id);
        $this->assertInstanceOf('Album\Entity\Album', $album);
        $this->assertEquals($data['artist'], $album->artist);

        $album->populate($data);
        $this->em->flush();

        $this->assertEquals($data['title'], $album->title);
    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanRemoveInsertedRecord($id)
    {
        $album = $this->em->getRepository('Album\Entity\Album')->find($id);
        $this->assertInstanceOf('Album\Entity\Album', $album);

        $this->em->remove($album);
        $this->em->flush();

        $album = $this->em->getRepository('Album\Entity\Album')->find($id);
        $this->assertEquals(false, $album);

        $dbh = $this->em->getConnection();
        $result = $dbh->exec("UPDATE sqlite_sequence SET seq = seq - 1 WHERE name='album';");
    }


    public function tearDown()
    {
        unset($this->sm);
        unset($this->em);

        parent::tearDown();
    }
}