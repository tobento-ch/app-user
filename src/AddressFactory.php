<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\User;

use Tobento\App\User\AddressFactoryInterface as AppAddressFactoryInterface;
use Tobento\Service\Repository\Storage\EntityFactory;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Storage\ItemsInterface;
use Tobento\Service\Storage\ItemInterface;
use Tobento\Service\User\AddressesFactoryInterface;
use Tobento\Service\User\AddressFactoryInterface;
use Tobento\Service\User\AddressesInterface;
use Tobento\Service\User\AddressInterface;
use Tobento\Service\Country\CountryRepositoryInterface;

/**
 * AddressFactory
 */
class AddressFactory extends EntityFactory implements AppAddressFactoryInterface
{
    /**
     * Create a new UserFactory.
     *
     * @param AddressFactoryInterface $addressFactory
     * @param AddressesFactoryInterface $addressesFactory
     * @param null|CountryRepositoryInterface $countryRepository
     * @param null|iterable|ColumnsInterface $columns
     */
    public function __construct(
        protected AddressFactoryInterface $addressFactory,
        protected AddressesFactoryInterface $addressesFactory,
        protected null|CountryRepositoryInterface $countryRepository = null,
        null|iterable|ColumnsInterface $columns = null,
    ) {
        parent::__construct($columns);
    }
    
    /**
     * Create an entity from array.
     *
     * @param array $attributes
     * @return AddressInterface The created entity.
     */
    public function createEntityFromArray(array $attributes): AddressInterface
    {
        // Process the columns reading:
        $attributes = $this->columns->processReading($attributes);
        
        $address = $this->addressFactory->createAddressFromArray($attributes);

        // Handle country:
        if (
            !empty($address->countryKey())
            && !is_null($this->countryRepository)
        ) {
            $country = $this->countryRepository->findCountry(
                code: $address->countryKey(),
                locale: $address->locale(),
            );

            if (!is_null($country)) {
                $address = $address->withCountry($country->name());
            }
        }
        
        return $address;
    }
    
    /**
     * Create addresses.
     *
     * @param mixed $addresses
     * @return AddressesInterface
     */
    public function createAddresses(mixed $addresses): AddressesInterface
    {
        if ($addresses instanceof AddressesInterface) {
            return $addresses;
        }
        
        if (is_iterable($addresses)) {
            
            $ads = $this->addressesFactory->createAddresses();
            
            foreach($addresses as $address) {
                if (is_array($address)) {
                    $ads->add($this->createEntityFromArray($address));
                } elseif ($address instanceof ItemInterface) {
                    $ads->add($this->createEntityFromArray($address->all()));
                } elseif ($address instanceof AddressInterface) {
                    $ads->add($address);
                }
            }
            
            return $ads;
        }
        
        if ($addresses instanceof AddressInterface) {
            return $this->addressesFactory->createAddresses($addresses);
        }        

        return $this->addressesFactory->createAddresses();
    }
}