<?php

namespace Tests;
use Illuminate\Support\Carbon;

use Laravel\Lumen\Testing\DatabaseMigrations;

class CRMProspectUnitTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }
    /**
     * Functional test for CRM prospect Comapny name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationCompanyNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Director/Owner mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationDirectorOrOwnerRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['director_or_owner' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'director_or_owner' => ['The director or owner field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect ROC number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationROCNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['roc_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Contact number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect email mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationEmailRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Address mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationAddressRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationPICNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Contact Number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationPICContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Registered By mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationRegisteredByRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['registered_by' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'registered_by' => ['The registered by field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Sector Type mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationSectorTypeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['sector_type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sector_type' => ['The sector type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Service type mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationServiceTypeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['prospect_service' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'prospect_service' => ['The prospect service field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect company name format validation 
     * 
     * @return void
     */
    public function testForProspectCompanyNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => 'ABC Firm123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect roc number format validation 
     * 
     * @return void
     */
    public function testForProspectROCNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['roc_number' => 'ABC494387$%%^^&']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect roc number unique validation 
     * 
     * @return void
     */
    public function testForProspectROCNumberUniqueValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/crm/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['roc_number' => 'APS6376', 'email' => 'testcrm2@gmail.com']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number has already been taken.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect director or owner format validation 
     * 
     * @return void
     */
    public function testForProspectDirectorOrOwnerFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['director_or_owner' => 'ABC494387$%%^^&']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'director_or_owner' => ['The director or owner format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect contact number size validation 
     * 
     * @return void
     */
    public function testForProspectContactNumberSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['contact_number' => 647348435879845798]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect contact number format validation 
     * 
     * @return void
     */
    public function testForProspectContactNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['contact_number' => 6473498.67]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect email Format validation 
     * 
     * @return void
     */
    public function testForProspectEmailFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['email' => 'gdshgsggsghvhs']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email must be a valid email address.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect email unique validation 
     * 
     * @return void
     */
    public function testForProspectemailUniqueValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/crm/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['email' => 'testcrm@gmail.com', 'roc_number' => 'APS63769']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email has already been taken.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect pic name Format validation 
     * 
     * @return void
     */
    public function testForProspectPICNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_name' => 'Test 63567236$%^^%']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC contact number size validation 
     * 
     * @return void
     */
    public function testForProspectPICContactNumberSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_contact_number' => 6473498867588767]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC contact number format validation 
     * 
     * @return void
     */
    public function testForProspectPICContactNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_contact_number' => 6473498.67]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC designation format validation 
     * 
     * @return void
     */
    public function testForProspectPICDesignationFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_designation' => 'HR1']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_designation' => ['The pic designation format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect bank account name format validation 
     * 
     * @return void
     */
    public function testForProspectBankAccountNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['bank_account_name' => 'HR14736848$%^^$%^^']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_name' => ['The bank account name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect bank account number format validation 
     * 
     * @return void
     */
    public function testForProspectBankAccountNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['bank_account_number' => 'HR1473^^$%^^']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_number' => ['The bank account number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect bank account number min size validation 
     * 
     * @return void
     */
    public function testForProspectBankAccountNumberMinSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['bank_account_number' => '4557']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_number' => ['The bank account number must be at least 5 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect bank account number max size validation 
     * 
     * @return void
     */
    public function testForProspectBankAccountNumberMaxSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['bank_account_number' => '781473684743783758347589743957']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_number' => ['The bank account number must not be greater than 17 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect tax id format validation 
     * 
     * @return void
     */
    public function testForProspectBankTaxIdFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['tax_id' => 'HR1473^^$%^^']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tax_id' => ['The tax id format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect tax id min size validation 
     * 
     * @return void
     */
    public function testForProspectTaxIdMinSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['tax_id' => 'H9743957']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tax_id' => ['The tax id must be at least 12 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect tax id max size validation 
     * 
     * @return void
     */
    public function testForProspectTaxIdMaxSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['tax_id' => 'HR1473684743783758347589743957']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tax_id' => ['The tax id must not be greater than 13 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect account receivable tax type format validation 
     * 
     * @return void
     */
    public function testForProspectAccountReceivableTaxTypeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['account_receivable_tax_type' => 'HR14736848$%^^$%^^']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'account_receivable_tax_type' => ['The account receivable tax type format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect account payable tax type format validation 
     * 
     * @return void
     */
    public function testForProspectAccountPayableTaxTypeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['account_payable_tax_type' => 'HR14736848$%^^$%^^']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'account_payable_tax_type' => ['The account payable tax type format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect registration 
     * 
     * @return void
     */
    public function testForProspectCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/crm/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Prospect Created Successfully']
        ]);
    }
    /**
     * Functional test for CRM prospect id mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationIDRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Comapny name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationCompanyNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['company_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect ROC number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationROCNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['roc_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Director/Owner mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationDirectorOrOwnerRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['director_or_owner' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'director_or_owner' => ['The director or owner field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Contact number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect email mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationEmailRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Address mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationAddressRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Contact Number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Registered By mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationRegisteredByRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['registered_by' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'registered_by' => ['The registered by field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Sector Type mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationSectorTypeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['sector_type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sector_type' => ['The sector type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, company name format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationCompanyNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['company_name' => 'company123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, roc number format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationROCNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['roc_number' => 'roc1235%^&%^']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update roc number unique validation 
     * 
     * @return void
     */
    public function testForProspectUpdateROCNumberUniqueValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['roc_number' => 'APS6377', 'email' => 'testcrm2@gmail.com']), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['id' => 2, 'roc_number' => 'APS6376', 'email' => 'testcrm2@gmail.com']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number has already been taken.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, director_or_owner format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationDirectorOrOwnerFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['director_or_owner' => 'Name2453%^^%$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'director_or_owner' => ['The director or owner format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, contact_number format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationContactNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['contact_number' => '73453%^^%$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, contact_number max size validation 
     * 
     * @return void
     */
    public function testForProspectUpdationContactNumberMaxSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['contact_number' => '734537438563745845875747']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update email unique validation 
     * 
     * @return void
     */
    public function testForProspectUpdateEmailUniqueValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['email' => 'testcrm@gmail.com', 'roc_number' => 'APS63769']), $this->getHeader());
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['id' => 2, 'email' => 'testcrm@gmail.com', 'roc_number' => 'APS63769']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email has already been taken.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, pic_name format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_name' => 'TestPIC2543']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, pic_contact_number format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICContactNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_contact_number' => '635625.88']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, pic_contact_number max size validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICContactNumberMaxSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_contact_number' => '6356254638756348765784658888']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, PIC Designation format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICDesignationFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_designation' => 'HR1']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_designation' => ['The pic designation format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, bank_account_name format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationBankAccountNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['bank_account_name' => 'HR1857647887%%$$%']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_name' => ['The bank account name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, bank_account_number format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationBankAccountNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['bank_account_number' => 'HR185764788']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_number' => ['The bank account number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, bank_account_number min size validation 
     * 
     * @return void
     */
    public function testForProspectUpdationBankAccountNumberMinSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['bank_account_number' => '788']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_number' => ['The bank account number must be at least 5 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, bank_account_number max size validation 
     * 
     * @return void
     */
    public function testForProspectUpdationBankAccountNumberMaxSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['bank_account_number' => '7885786945895869589875698']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'bank_account_number' => ['The bank account number must not be greater than 17 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, tax_id format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationTaxIdFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['tax_id' => 'HR18588&*&^%']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tax_id' => ['The tax id format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, tax_id min size validation 
     * 
     * @return void
     */
    public function testForProspectUpdationTaxIdMinSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['tax_id' => 'Tax64873']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tax_id' => ['The tax id must be at least 12 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, tax_id max size validation 
     * 
     * @return void
     */
    public function testForProspectUpdationTaxIdMaxSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['tax_id' => 'Tax648735465657687689799']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tax_id' => ['The tax id must not be greater than 13 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, account_receivable_tax_type format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationAccountReceivableTaxTypeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['account_receivable_tax_type' => 'HR18588&*&']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'account_receivable_tax_type' => ['The account receivable tax type format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect update, account_payable_tax_type format validation 
     * 
     * @return void
     */
    public function testForProspectUpdationAccountPayableTaxTypeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['account_payable_tax_type' => 'HR18588&*&']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'account_payable_tax_type' => ['The account payable tax type format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect updation 
     * 
     * @return void
     */
    public function testForProspectUpdation(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Prospect Updated Successfully']
        ]);
    }
    /**
     * Functional test to list CRM prospects
     * 
     * @return void
     */
    public function testForProspectList(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/list', ['search' => '', 'filter' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
        ]);
    }
    /**
     * Functional test to list CRM prospects with search
     * 
     * @return void
     */
    public function testForProspectListWithSearch(): void
    {
        $this->testForProspectCreation();
        $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => 'XYZ Firm']), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/list', ['search' => 'XY', 'filter' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
        ]);
    }
    /**
     * Functional test to list CRM prospects with filter
     * 
     * @return void
     */
    public function testForProspectListWithFilter(): void
    {
        $this->testForProspectCreation();
        $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => 'XYZ Firm', 'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"]])]), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/list', ['search' => '', 'filter' => 3], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
        ]);
    }
    /**
     * Functional test to show CRM prospect
     * 
     * @return void
     */
    public function testForProspectShow(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                        'id',
                        'company_name',
                        'roc_number',
                        'director_or_owner',
                        'contact_number',
                        'email',
                        'address',
                        'pic_name',
                        'pic_contact_number',
                        'pic_designation',
                        'registered_by',
                        'registered_by_name',
                        'prospect_services',
                        'prospect_login_credentials'
                    ]
                ]
        ]);
    }
    /**
     * Functional test to show Companies Dropdown
     * 
     * @return void
     */
    public function testForCompaniesDropDown(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/dropDownCompanies', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                        'id',
                        'company_name'
                    ]
                ]
        ]);
    }
    /**
     * Functional test to show Prospect Details
     * 
     * @return void
     */
    public function testForGetProspectDetails(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/getProspectDetails', ["id" => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                        'id',
                        'company_name',
                        'contact_number',
                        'email',
                        'pic_name'
                    ]
                ]
        ]);
    }
    /**
     * @return void
     */
    public function creationSeeder(): void
    {
        $this->artisan("db:seed --class=ServiceSeeder");
        $this->artisan("db:seed --class=SystemTypeSeeder");
        $payload =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
        ];   
        $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $payload =  [
            'name' => 'Administrator'
        ];
        $this->json('POST', 'api/v1/role/create', $payload, $this->getHeader(false));
       
        $payload = [
            'employee_name' => 'Test', 
            'gender' => 'Female', 
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'), 
            'ic_number' => 222223434, 
            'passport_number' => 'ADI', 
            'email' => 'test@gmail.com', 
            'contact_number' => 238467,
            'address' => 'Addres', 
            'postcode' => 2344, 
            'position' => 'Position', 
            'branch_id' => 1,
            'role_id' => 1, 
            'salary' => 67.00, 
            'status' => 1, 
            'city' => 'ABC', 
            'state' => 'Malaysia'
        ];
        $this->json('POST', 'api/v1/employee/create', $payload, $this->getHeader(false));

        $payload =  [
            'sector_name' => 'Agriculture',
            'sub_sector_name' => 'Agriculture'
        ];  
        $this->json('POST', 'api/v1/sector/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['company_name' => 'ABC Firm', 'contract_type' => 'Zero Cost', 'roc_number' => 'APS6376', 'director_or_owner' => 'Test', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'address' => 'Coimbatore', 'pic_name' => 'PICTest', 'pic_contact_number' => '764859694', 'pic_designation' => 'Manager', 'registered_by' => 1, 'sector_type' => 1, 'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "Total Management"]])];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'company_name' => 'ABC Firm', 'contract_type' => 'Normal', 'roc_number' => 'APS6376', 'director_or_owner' => 'Test', 'contact_number' => '76845697', 'email' => 'test@gmail.com', 'address' => 'Coimbatore', 'pic_name' => 'PICTest', 'pic_contact_number' => '764859694', 'pic_designation' => 'Manager', 'registered_by' => 1, 'sector_type' => 1];
    }
}
