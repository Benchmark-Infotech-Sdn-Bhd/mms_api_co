<?php

namespace App\Imports;

use App\Services\CRMServices;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use stdClass;

class CrmImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected const CHUNK_ROW = 250;
    const SYSTEM_NAMES = ['FWCMS', 'FOMEMA', 'Email Login Credentials', 'EPLKS', 'Myfuture Jobs', 'ESD', 'Pin Keselamatan'];
    const USERNAME_SUFFIXES = ['fwcms_username', 'fomema_username', 'email_login_username', 'eplks_username', 'myfuture_jobs_username', 'esd_username', 'pin_keselamatan_username'];
    const PASSWORD_SUFFIXES = ['fwcms_password', 'fomema_password', 'email_login_password', 'eplks_password', 'myfuture_jobs_password', 'esd_password', 'pin_keselamatan_password'];

    /**
     * @var CRMServices
     */
    private CRMServices $crmServices;
    private mixed $parameters;
    private string $bulkUpload;

    /**
     * Constructs a new instance of the class.
     *
     * @param mixed $parameters The parameters for the constructor.
     * @param CRMServices $crmServices The CRMServices object used for CRM operations.
     * @param string $bulkUpload The bulk upload information (optional).
     *
     * @return void
     */
    public function __construct($parameters, CRMServices $crmServices, $bulkUpload = '')
    {
        $this->parameters = $parameters;
        $this->bulkUpload = $bulkUpload;
        $this->crmServices = $crmServices;
    }

    /**
     * Create a credential object.
     *
     * @param mixed $systemId The ID of the system.
     * @param string $systemName The name of the system.
     * @param string $username The username for the credential.
     * @param string $password The password for the credential.
     *
     * @return stdClass The created credential object.
     */
    private function createCredentialObject($systemId, $systemName, $username, $password)
    {
        $credential = new stdClass();
        $credential->system_id = $systemId;
        $credential->system_name = $systemName;
        $credential->username = $username;
        $credential->password = $password;

        return $credential;
    }

    /**
     * Create a credential object.
     *
     * @param mixed $id The ID of the service.
     * @param string $name The type of the service.
     *
     * @return stdClass The created credential object.
     */
    private function createProspectServiceObject($id, $name)
    {
        $credential = new stdClass();
        $credential->service_id = $id;
        $credential->service_name = $name;

        return $credential;
    }

    /**
     * Models a row of data and creates a CRM object.
     *
     * @param array $row The array containing the row data.
     *
     * @return void
     */
    public function model(array $row)
    {
        try {
            Log::info('Row Data', $row);

            // Create prospectService
            $prospectService[0] = $this->createProspectServiceObject($row['service_type'], Config::get('services.CRM_MODULE_TYPE')[$row['service_type'] - 1]);

            // Create loginCredential using loop
            for ($i = 0; $i < 7; $i++) {
                $loginCredential[$i] = $this->createCredentialObject($i + 1, self::SYSTEM_NAMES[$i], $row[self::USERNAME_SUFFIXES[$i]], $row[self::PASSWORD_SUFFIXES[$i]]);
            }

            // Desired keys from $row
            //$desiredKeys = ['company_name', 'contract_type', 'roc_number', 'director_name', 'contact_number', 'email', 'address', 'pic_name', 'pic_contact_number', 'designation', 'registered_by', 'sector_type', 'bank_acc_name', 'bank_acc_number', 'tax_id_number', 'created_by', 'modified_by', 'fomnext_company_name'];
            // Required parameters for Request object
            //$parameters = array_intersect_key($row, array_flip($desiredKeys));
            $parameters['company_name'] = $row['company_name'];
            $parameters['contract_type'] = $row['contract_type'];
            $parameters['roc_number'] = $row['roc_number'];
            $parameters['director_or_owner'] = $row['director_name'];
            $parameters['contact_number'] = $row['contact_number'];
            $parameters['email'] = $row['email'];                        
            $parameters['address'] = $row['address'];
            $parameters['pic_name'] = $row['pic_name'];
            $parameters['pic_contact_number'] = $row['pic_contact_number'];
            $parameters['pic_designation'] = $row['designation'];
            $parameters['registered_by'] = $row['registered_by'];
            $parameters['sector_type'] = $row['sector_type'];

            $parameters['bank_account_name'] = $row['bank_acc_name'];
            $parameters['bank_account_number'] = $row['bank_acc_number'];
            $parameters['tax_id'] = $row['tax_id_number'];

            $parameters['created_by'] = $row['created_by'] ?? 0;
            $parameters['modified_by'] = $row['modified_by'] ?? 0;
            $parameters['company_id'] = $row['fomnext_company_name'];

            $parameters['prospect_service'] = json_encode($prospectService);
            $parameters['login_credential'] = json_encode($loginCredential);

            $crmParameter = new Request($parameters);

            Log::info('data', $crmParameter->toArray());
            $return = $this->crmServices->create($crmParameter);
            //Log::info('data', $return);
        } catch (Exception $exception) {
            Log::error('Error', ['message' => $exception->getMessage()]);
        }
    }

    /**
     * Returns the chunk size for processing.
     *
     * @return int The chunk size for processing.
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}
