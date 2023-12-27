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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['company_name' => '']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['register_number' => '']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['country' => '']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['state' => '']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['company_name' => 'TestCompany468374']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['register_number' => 'SGT748&%&*']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['country' => 'India7487']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', array_merge($this->creationData(), ['state' => 'TamilNadu643874']), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.'],
                "register_number" => ["The register number has already been taken."]
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['company_name' => '']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['register_number' => '']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['country' => '']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['state' => '']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['company_name' => 'TestCompany468374']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['register_number' => 'SGT748&%&*']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['country' => 'India7487']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', array_merge($this->updationData(), ['state' => 'TamilNadu643874']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/update', $this->updationData(), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/updateStatus', ['id' => 2, 'status' => 0], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/list', ['search' => ''], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/show', ['id' => 1], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/subsidiaryDropDown', ['current_company_id' => 2], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/subsidiaryDropDown', [], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/assignSubsidiary', ['subsidiary_company' => [2], 'parent_company_id' => 1], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/assignModule', array_merge($this->assignModuleData(), ['company_id' => '']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/assignModule', array_merge($this->assignModuleData(), ['modules' => '']), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/company/assignModule', $this->assignModuleData(), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
        $this->json('POST', 'api/v1/company/assignModule', $this->assignModuleData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/company/moduleList', ['company_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
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
        return ['id' => 2, 'company_name' => 'Test Company', 'register_number' => 'APS646-46876', 'country' => 'India', 'state' => 'TamilNadu', 'system_color' => '#cesser', 'file_url' => 'test.png'];
    }
    /**
     * @return array
     */
    public function assignModuleData(): array
    {
        return ['company_id' => 1, 'modules' => [1]];
    }
}
