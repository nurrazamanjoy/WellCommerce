<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 *
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace WellCommerce\Bundle\AppBundle\Manager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;
use WellCommerce\Bundle\AppBundle\Entity\Dictionary;
use WellCommerce\Bundle\AppBundle\Entity\Locale;
use WellCommerce\Bundle\CoreBundle\Manager\AbstractManager;

/**
 * Class DictionaryManager
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
final class DictionaryManager extends AbstractManager
{
    /**
     * @var KernelInterface
     */
    protected $kernel;
    
    /**
     * @var array|\WellCommerce\Bundle\AppBundle\Entity\Locale[]
     */
    protected $locales;
    
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;
    
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var array
     */
    protected $translations = [];
    
    /**
     * Synchronizes database and filesystem translations
     */
    public function syncDictionary()
    {
        $this->kernel           = $this->get('kernel');
        $this->locales          = $this->getLocales();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->filesystem       = new Filesystem();
        
        foreach ($this->locales as $locale) {
            $this->updateFilesystemTranslationsForLocale($locale);
        }
        
        $this->synchronizeDatabaseTranslations();
    }
    
    protected function updateFilesystemTranslationsForLocale(Locale $locale)
    {
        $fsTranslations     = $this->getTranslatorHelper()->getMessages($locale->getCode());
        $dbTranslations     = $this->getDatabaseTranslations($locale);
        $mergedTranslations = array_replace_recursive($fsTranslations, $dbTranslations);
        $filename           = sprintf('wellcommerce.%s.yml', $locale->getCode());
        $path               = $this->getFilesystemTranslationsPath() . DIRECTORY_SEPARATOR . $filename;
        $content            = Yaml::dump($mergedTranslations, 6);
        $this->filesystem->dumpFile($path, $content);
        
        foreach ($mergedTranslations as $identifier => $translation) {
            $this->translations[$identifier][$locale->getCode()] = $translation;
        }
    }
    
    protected function synchronizeDatabaseTranslations()
    {
        $this->purgeTranslations();
        
        $em = $this->getDoctrineHelper()->getEntityManager();
        
        foreach ($this->translations as $identifier => $translation) {
            /** @var Dictionary $dictionary */
            $dictionary = $this->factory->create();
            $dictionary->setIdentifier($identifier);
            foreach ($translation as $locale => $value) {
                $dictionary->translate($locale)->setValue($value);
            }
            $dictionary->mergeNewTranslations();
            $em->persist($dictionary);
        }
        
        $em->flush();
    }
    
    protected function purgeTranslations()
    {
        $em             = $this->getEntityManager();
        $batchSize      = 20;
        $i              = 0;
        $q              = $em->createQuery('SELECT d from ' . Dictionary::class . ' d');
        $iterableResult = $q->iterate();
        while (($row = $iterableResult->next()) !== false) {
            $em->remove($row[0]);
            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear();
            }
            ++$i;
        }
        
        $em->flush();
    }
    
    /**
     * Returns an array containing all previously imported translations
     *
     * @param Locale $locale
     *
     * @return array
     */
    protected function getDatabaseTranslations(Locale $locale)
    {
        $messages   = [];
        $collection = $this->repository->getCollection();
        
        $collection->map(function (Dictionary $dictionary) use ($locale, &$messages) {
            $messages[$dictionary->getIdentifier()] = $dictionary->translate($locale->getCode())->getValue();
        });
        
        return $messages;
    }
    
    
    protected function getFilesystemTranslationsPath()
    {
        $kernelDir = $this->kernel->getRootDir();
        
        return $kernelDir . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'translations';
    }
}


