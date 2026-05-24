<?php

declare(strict_types=1);

namespace HenriqueAmrl\AsaasPhp\Dto;

final class Customer
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $cpfCnpj,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $mobilePhone = null,
        public readonly ?string $address = null,
        public readonly ?string $addressNumber = null,
        public readonly ?string $complement = null,
        public readonly ?string $province = null,
        public readonly ?int $city = null,
        public readonly ?string $cityName = null,
        public readonly ?string $state = null,
        public readonly ?string $country = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $personType = null,
        public readonly bool $deleted = false,
        public readonly ?string $additionalEmails = null,
        public readonly ?string $externalReference = null,
        public readonly bool $notificationDisabled = false,
        public readonly ?string $observations = null,
        public readonly bool $foreignCustomer = false,
        public readonly ?string $groupName = null,
        public readonly ?string $municipalInscription = null,
        public readonly ?string $stateInscription = null,
        public readonly ?string $company = null,
        public readonly ?string $object = null,
        public readonly ?string $dateCreated = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            cpfCnpj: (string) ($data['cpfCnpj'] ?? ''),
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            mobilePhone: isset($data['mobilePhone']) ? (string) $data['mobilePhone'] : null,
            address: isset($data['address']) ? (string) $data['address'] : null,
            addressNumber: isset($data['addressNumber']) ? (string) $data['addressNumber'] : null,
            complement: isset($data['complement']) ? (string) $data['complement'] : null,
            province: isset($data['province']) ? (string) $data['province'] : null,
            city: isset($data['city']) ? (int) $data['city'] : null,
            cityName: isset($data['cityName']) ? (string) $data['cityName'] : null,
            state: isset($data['state']) ? (string) $data['state'] : null,
            country: isset($data['country']) ? (string) $data['country'] : null,
            postalCode: isset($data['postalCode']) ? (string) $data['postalCode'] : null,
            personType: isset($data['personType']) ? (string) $data['personType'] : null,
            deleted: (bool) ($data['deleted'] ?? false),
            additionalEmails: isset($data['additionalEmails']) ? (string) $data['additionalEmails'] : null,
            externalReference: isset($data['externalReference']) ? (string) $data['externalReference'] : null,
            notificationDisabled: (bool) ($data['notificationDisabled'] ?? false),
            observations: isset($data['observations']) ? (string) $data['observations'] : null,
            foreignCustomer: (bool) ($data['foreignCustomer'] ?? false),
            groupName: isset($data['groupName']) ? (string) $data['groupName'] : null,
            municipalInscription: isset($data['municipalInscription']) ? (string) $data['municipalInscription'] : null,
            stateInscription: isset($data['stateInscription']) ? (string) $data['stateInscription'] : null,
            company: isset($data['company']) ? (string) $data['company'] : null,
            object: isset($data['object']) ? (string) $data['object'] : null,
            dateCreated: isset($data['dateCreated']) ? (string) $data['dateCreated'] : null,
        );
    }
}
