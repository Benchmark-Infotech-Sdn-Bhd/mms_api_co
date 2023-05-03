<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DirectRecruitmentProposalTest extends TestCase
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
     * A test method for validate create proposal
     * 
     * @return void
     */
    public function testAddProposalRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['crm_prospect_id' => '', 'quota_applied' => '', 'person_incharge' => '', 'cost_quoted' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "crm_prospect_id" => ["The crm prospect id field is required."],
                "quota_applied" => ["The quota applied field is required."],
                "person_incharge" => ["The person incharge field is required."],
                "cost_quoted" => ["The cost quoted field is required."],
            ]
        ]);
    }

    /**
     * Functional test to validate minimum/maximum characters for fields in proposal creation
     * 
     * @return void
     */
    public function testAddProposalMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['crm_prospect_id' => '1', 'quota_applied' => '123456789', 'person_incharge' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt', 'cost_quoted' => '2']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "quota_applied" => ["The quota applied must not be greater than 3 characters."],
                "person_incharge" => ["The person incharge must not be greater than 150 characters."],
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in proposal creation
     * 
     * @return void
     */
    public function testAddProposalFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['crm_prospect_id' => '1', 'quota_applied' => 'dsdsddd', 'person_incharge' => '23234 dfd', 'cost_quoted' => 'sdsd']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "quota_applied" => ["The quota applied format is invalid."],
                "person_incharge" => ["The person incharge format is invalid."],
                "cost_quoted" => ["The cost quoted format is invalid."],
            ]
        ]);
    }

    /**
     * Functional test for create proposal
     */
    public function testForAddProposal(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', $this->addProposalData(), $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'message'
            ]
        ]);
    }
    /**
     * @return array
     */
    public function addProposalData(): array
    {
        return ['id' => 1, 'crm_prospect_id' => 1, 'quota_applied' => 22, 'person_incharge' => 'test', 
        'cost_quoted' => 10.22, 'remarks' => 'test'];
    }
}