<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class CompanyUnitTest extends TestCase
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
     * Functional test for Company Creation company name mandatory field validation
     * 
     * @return void
     */
    public function testForCompanyCreationCompanyNameRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['company_name' => '']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Creation register number mandatory field validation 
     * 
     * @return void
     */
    public function testForCompanyCreationRegisterNumberRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['register_number' => '']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'register_number' => ['The register number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Creation country mandatory field validation 
     * 
     * @return void
     */
    public function testForCompanyCreationCountryRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['country' => '']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'country' => ['The country field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Creation state mandatory field validation 
     * 
     * @return void
     */
    public function testForCompanyCreationStateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['state' => '']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Creation company name format validation 
     * 
     * @return void
     */
    public function testForCompanyCreationCompanyNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['company_name' => 'TestCompany4683746']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company creation register number format validation 
     * 
     * @return void
     */
    public function testForCompanyCreationRegisterNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['register_number' => 'SGT748&%&*']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'register_number' => ['The register number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company creation country format validation 
     * 
     * @return void
     */
    public function testForCompanyCreationCountryFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['country' => 'India7487']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'country' => ['The country format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company creation state format validation 
     * 
     * @return void
     */
    public function testForCompanyCreationStateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['state' => 'TamilNadu643874']), $this->getSuperHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company creation
     * 
     * @return void
     */
    public function testForCompanyCreation(): void
    {
        $response = $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Company Created Successfully']
        ]);
    }
    /**
     * Functional test for Company Updation id mandatory field validation
     * 
     * @return void
     */
    public function testForCompanyUpdationIdRequiredFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation company name mandatory field validation
     * 
     * @return void
     */
    public function testForCompanyUpdationCompanyNameRequiredFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['company_name' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation register number mandatory field validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationRegisterNumberRequiredFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['register_number' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'register_number' => ['The register number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation country mandatory field validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationCountryRequiredFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['country' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'country' => ['The country field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation state mandatory field validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationStateRequiredFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['state' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation company name format validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationCompanyNameFormatValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['company_name' => 'TestCompany468374']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation register number format validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationRegisterNumberFormatValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['register_number' => 'SGT748&%&*']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'register_number' => ['The register number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company Updation country format validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationCountryFormatValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['country' => 'India7487']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'country' => ['The country format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company updation state format validation 
     * 
     * @return void
     */
    public function testForCompanyUpdationStateFormatValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['state' => 'TamilNadu643874']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Company updation
     * 
     * @return void
     */
    public function testForCompanyUpdation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/update', $this->updationData(), $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Company Updated Successfully']
        ]);
    }
    /**
     * Functional test to update Company status
     * 
     * @return void
     */
    public function testForCompanyStatusUpdation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/updateStatus', ['id' => 1, 'status' => 0], $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Company Status Updated Successfully']
        ]);
    }
    /**
     * Functional test for Companies list
     * 
     * @return void
     */
    public function testForCompaniesListWithSearch(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/list', ['search' => ''], $this->getSuperHeader(false));
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
     * Functional test for Company Show
     * 
     * @return void
     */
    public function testForCompanyShow(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/show', ['id' => 1], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'id',
                    'company_name',
                    'register_number',
                    'country',
                    'state',
                    'pic_name',
                    'role',
                    'status',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
        ]);
    }
    /**
     * Functional test for subsidiary dropdown
     * 
     * @return void
     */
    public function testForSubsidiaryDropdown(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/subsidiaryDropDown', ['current_company_id' => 2], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for parent DropDown
     * 
     * @return void
     */
    public function testForParentDropDown(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/subsidiaryDropDown', [], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test to assign subsidiary
     * 
     * @return void
     */
    public function testForAssignSubsidiary(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignSubsidiary', ['subsidiary_company' => [2], 'parent_company_id' => 1], $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Subsidiary Updated Successfully']
        ]);
    }
    /**
     * Functional test for list user company
     * 
     * @return void
     */
    public function testForListUserCompany(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/listUserCompany', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test to update company id
     * 
     * @return void
     */
    public function testForCompanyIdUpdation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/updateCompanyId', ['company_id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Company ID Updated Successfully']
        ]);
    }
    /**
     * Functional test for subsidiary dropdown based on parent
     * 
     * @return void
     */
    public function testForSubsidiaryDropdownBasedOnParent(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/subsidiaryDropdownBasedOnParent', ['company_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for company dropdown
     * 
     * @return void
     */
    public function testForCompanyDropdown(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/dropdown', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for Company assign module field validation 
     * 
     * @return void
     */
    public function testForCompanyAssignModuleCompanyIdFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignModule', array_merge($this->assignModuleData(), ['company_id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_id' => ['The company id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company assign module field validation 
     * 
     * @return void
     */
    public function testForCompanyAssignModuleModulesFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignModule', array_merge($this->assignModuleData(), ['modules' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'modules' => ['The modules field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company assign module
     * 
     * @return void
     */
    public function testForCompanyAssignModule(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignModule', $this->assignModuleData(), $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Module Assigned Successfully']
        ]);
    }
    /**
     * Functional test for Company assign module list
     * 
     * @return void
     */
    public function testForCompanyAssignModuleList(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $this->json('POST', 'api/v1/company/assignModule', $this->assignModuleData(), $this->getSuperHeader(false));
        $response = $this->json('POST', 'api/v1/company/moduleList', ['company_id' => 1], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for Company assign features company id validation 
     * 
     * @return void
     */
    public function testForCompanyAssignFeaturesCompanyIdFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignFeature', array_merge($this->assignFeatureData(), ['company_id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_id' => ['The company id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company assign features field validation 
     * 
     * @return void
     */
    public function testForCompanyAssignFeaturesFeaturesFieldValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignFeature', array_merge($this->assignFeatureData(), ['features' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'features' => ['The features field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company assign features
     * 
     * @return void
     */
    public function testForCompanyAssignFeature(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/assignFeature', $this->assignFeatureData(), $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Feature Assigned Successfully']
        ]);
    }
    /**
     * Functional test for Company assign module list
     * 
     * @return void
     */
    public function testForCompanyAssignFeatureList(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $this->json('POST', 'api/v1/company/assignFeature', $this->assignFeatureData(), $this->getSuperHeader(false));
        $response = $this->json('POST', 'api/v1/company/moduleList', ['company_id' => 1], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for Company account system create company id validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateCompanyIDValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['company_id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_id' => ['The company id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create title validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateTitleValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['title' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'title' => ['The title field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create url validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateURLValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['url' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'url' => ['The url field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create client id validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateClientIdValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['client_id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'client_id' => ['The client id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create client secret validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateClientSecretValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['client_secret' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'client_secret' => ['The client secret field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create tenant id validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreatetenantIdValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['tenant_id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'tenant_id' => ['The tenant id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create access token validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateAccessTokenValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['access_token' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'access_token' => ['The access token field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create refresh token validation
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreateRefreshTokenValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', array_merge($this->accountSystemCreateData(), ['refresh_token' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'refresh_token' => ['The refresh token field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Company account system create
     * 
     * @return void
     */
    public function testForCompanyAccountSystemCreate(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/accountSystem/update', $this->accountSystemCreateData(), $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Account System Updated Successfully']
        ]);
    }
    /**
     * Functional test for Company account system show
     * 
     * @return void
     */
    public function testForCompanyAccountSystemShow(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $this->json('POST', 'api/v1/company/accountSystem/update', $this->accountSystemCreateData(), $this->getSuperHeader(false));
        $response = $this->json('POST', 'api/v1/company/accountSystem/show', ['company_id' => 1], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for Company account system delete
     * 
     * @return void
     */
    public function testForCompanyAccountSystemDelete(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $this->json('POST', 'api/v1/company/accountSystem/update', $this->accountSystemCreateData(), $this->getSuperHeader(false));
        $response = $this->json('POST', 'api/v1/company/accountSystem/delete', ['company_id' => 1, 'title' => 'XERO'], $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Account System Deleted Successfully']
        ]);
    }
    /**
     * Functional test for Company Email Configuration Notification List
     * 
     * @return void
     */
    public function testForCompanyEmailConfigurationNotificationList(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/emailConfiguration/notificationList', ['company_id' => 1], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }

    /**
     * Functional test for Company Email Configuration show
     * 
     * @return void
     */
    public function testForCompanyEmailConfigurationShow(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $this->json('POST', 'api/v1/company/emailConfiguration/save', $this->companyEmailConfigurationData(), $this->getSuperHeader(false));
        $response = $this->json('POST', 'api/v1/company/emailConfiguration/show', ['company_id' => 1], $this->getSuperHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }

    /**
     * Functional test for Company Email Configuration save
     * 
     * @return void
     */
    public function testForCompanyEmailConfigurationSave(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/emailConfiguration/save', $this->companyEmailConfigurationData(), $this->getSuperHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Email Configuration Saved Successfully']
        ]);
    }

    /**
     * Functional test for Company Email Configuration save company id validation
     * 
     * @return void
     */
    public function testForCompanyEmailConfigurationSaveCompanyIdValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/emailConfiguration/save', array_merge($this->companyEmailConfigurationData(), ['company_id' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_id' => ['The company id field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Company Email Configuration save notification settings validation
     * 
     * @return void
     */
    public function testForCompanyEmailConfigurationSaveNotificationDettingsValidation(): void
    {
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getSuperHeader());
        $response = $this->json('POST', 'api/v1/company/emailConfiguration/save', array_merge($this->companyEmailConfigurationData(), ['notification_settings' => '']), $this->getSuperHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'notification_settings' => ['The notification settings field is required.']
            ]
        ]);
    }

    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['company_name' => 'Test Company', 'register_number' => 'APS646-46876', 'country' => 'India', 'state' => 'TamilNadu', 'pic_name' => '', 'role' => 'Admin', 'parent_id' => 0, 'system_color' => '#cesser', 'file_url' => 'test.png'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'company_name' => 'Test Company', 'register_number' => 'APS646-468766', 'country' => 'India', 'state' => 'TamilNadu', 'system_color' => '#cesser', 'file_url' => 'test.png'];
    }
    /**
     * @return array
     */
    public function assignModuleData(): array
    {
        return ['company_id' => 1, 'modules' => [1]];
    }
    /**
     * @return array
     */
    public function assignFeatureData(): array
    {
        return ['company_id' => 1, 'features' => [16]];
    }
    /**
     * @return array
     */
    public function accountSystemCreateData(): array
    {
        return [
            "company_id"  =>  1,
            "title" =>  "XERO",
            "url" =>  "https => //api.xero.com/api.xro/2.0/",
            "client_id" => "0868F1CE1BAA46BA8EC8F178DAB75B2B",
            "client_secret" => "rrdE8qWO1EBFftigkjD_Skhs-8y5QWmUlq6z0IqmJgiUpPiW",
            "tenant_id" =>  "164b8ba4-e867-480b-9d64-2b801cfe99d4",
            "access_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6IjFDQUY4RTY2NzcyRDZEQzAyOEQ2NzI2RkQwMjYxNTgxNTcwRUZDMTkiLCJ0eXAiOiJKV1QiLCJ4NXQiOiJISy1PWm5jdGJjQW8xbkp2MENZVmdWY09fQmsifQ.eyJuYmYiOjE2OTg4NDgxMDEsImV4cCI6MTY5ODg0OTkwMSwiaXNzIjoiaHR0cHM6Ly9pZGVudGl0eS54ZXJvLmNvbSIsImF1ZCI6Imh0dHBzOi8vaWRlbnRpdHkueGVyby5jb20vcmVzb3VyY2VzIiwiY2xpZW50X2lkIjoiMDg2OEYxQ0UxQkFBNDZCQThFQzhGMTc4REFCNzVCMkIiLCJzdWIiOiI3ZGI2MTM2OWE5MTQ1YjJmODBiOWQxYWE3MmI4ZThjNCIsImF1dGhfdGltZSI6MTY5NzA3OTIwNCwieGVyb191c2VyaWQiOiI2ODI0NGEwMS0zY2JjLTQxYjAtODE0Ni1mYzM4NTQzN2RhZWQiLCJnbG9iYWxfc2Vzc2lvbl9pZCI6ImYwNzFlMGE2NGY2YTRjNWVhYzhiYTlkM2UzNjMyZGYxIiwic2lkIjoiZjA3MWUwYTY0ZjZhNGM1ZWFjOGJhOWQzZTM2MzJkZjEiLCJqdGkiOiJBMzMxQzc3NDMzMTVFMEY5NjFFNUQxQUFGQTEwQ0MxQiIsImF1dGhlbnRpY2F0aW9uX2V2ZW50X2lkIjoiZmQyZDhjMWEtYWQ5Zi00MmRhLWJiNGEtNmVkOTM1YzEzZjU0Iiwic2NvcGUiOlsiYWNjb3VudGluZy5zZXR0aW5ncyIsImFjY291bnRpbmcudHJhbnNhY3Rpb25zIiwiYWNjb3VudGluZy5jb250YWN0cyIsIm9mZmxpbmVfYWNjZXNzIl0sImFtciI6WyJwd2QiXX0.rZ-9Wv5qsEHIh6HkCABoVWvJ-nT0V8ek_knCG3p2_ygdhi7SyTtUNZ4MHB04W5n17ZjzAGhFX5grc5goUDQn5yf-czuhhm4tqq_42HfryMZIoKaVK-y7vHLskSgLpmsVRpgzGxcoINCnMGrWq8P2B8vkaqmo0xuyqX4Xz6f6FGfluPcMFc5AzXST0MI6R18CItK3_d0v2xcaMEb5PJQ1EQDvqgv9HfB-Viroqb8g6kQlY68jr9yWMlaIyonV5BSFuV-I5Q_iksqm0nrYY8MO2AizmxT6Vgs3aIGylFaNbOfZ92N21ei-c4qoPHvCkfme67ESZ4NxRKG_n_gaDg4hAg",
            "refresh_token" => "3ODPNZS07IutHX1itXx_tHSVFB4P_454pS6rhhAs7sg",
            "redirect_url" => "testredreictups.com",
            "remarks" => "test remarkups"
        ];
    }
    /**
     * @return array
     */
    public function companyEmailConfigurationData(): array
    {
        return [
            "company_id"  =>  1,
            "notification_settings" =>  json_encode([["notification_id"=>1, "renewal_duration_in_days"=>1, "renewal_frequency_cycle"=>"Weekly", "expired_notification_status" => 1, "expired_duration_in_days" => 30, "expired_frequency_cycle" => "Weekly"]])
        ];
    }
}
