<?php

namespace App\Services;

session_start();

use Illuminate\Support\Facades\Config;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Data\IPPInvoice;
use QuickBooksOnline\API\Data\IPPCustomer;

class QuickBooksServices
{
    /**
     * QuickBooksServices constructor.
     */
    public function __construct()
    {
        /* $this->dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => "ABBIl94z3LbVIcahjPl7CP5EpNcJMewnxymypNzI7izuqDlKQR",
            'ClientSecret' => "OiZJBbgyXbiSPzBRALYUCIQevnMhpDft1YfH9StK",
            'accessTokenKey' => 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..mVTGrVwpfkX0n85B13wYRg.MTAlnZgztgnIMeAFF7474sv7vlZ7RehS9BVDcJ6nnMTmZBCy1iLM1R7JGFqz8FF4hFiRTXjr7VLVamsGy8mNQPRXRb3QMK3Lo4L90f1fYjOIOEwkg0aBdciAAwDI2tIEh3QkoMEbeLNd1Y7OD4_68cQt2fKIbPOmTvGomoLZ0T3XZhosq424aB7HlgygUKxzr_NZt_-sbsQZsu1oPVYsv-jUulMZffVXvoxMC4GIqS8YBFFugRJXyVQYSupoG3LzGJ6wxJiKvGFnDjv181n15xL2WpJuEabnBkbeF6Fw9fvdLHZiMZb_mwghIm5kzewOrrki1i2paWcbBLwRr27Ecr-bWBOzuO6S6r2zX-TIf82rBvABPzx9V4Ft2cHcEXwCDaUo9_LC2NtUa3i_VOqlEPuUi9Pj9bPN6beEwOWWE8KnWZ_NjCeYTHAQXpd5HnCC6QjWBmtaNOTNg0MOoNN9U4dRtiG1zHBJtg3A4uOzRM0VshTwYNt9CoeA2ky4Ib4byp0I9GoZq-9_jiG1-20RafTjbkq658EvwpYst-xGRMH9jevD7TGCAlw-XEp5PBKtxs2RieRdFC34SyD5xgwhUy9l5SXZDCkaJ43rng0Kl9rW4ZeEwZ0vDsIoraMnwXM6gVZkzzw-WgRwOLwl5bK0I_UnTlL7mdeG6370y0xmzcUGrX3xQVI2sOLHYxZUilMhmXaW13ls04QONGglDsMp0zz_kKApnABMWdnl7J5xMQYH_eO5RG6EPJLSNh_QrtYwMO0kR0J5CRphtfcp_Iwo3oI3S5jl8WpWgEXDGn9CvbbRBPPX1OVmjGe5bXsMfsXBX0QXuJeYrCeorWLDqZEGxp-EBMXma7zbzhZV_eBkc-uPWK_MWLexTzQH3sd9bbdL.0LjzLY_uWHTQxxy7imaAMg',
            'refreshTokenKey' => "AB11710599347CcZ1vy2gbVf1C8zsetNaJkEfwlIah97NSmZ61",
            'QBORealmId' => 4620816365362100120,
            'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
        )); */

        $this->dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => Config::get('services.QUICKBOOKS_CLIENT_ID'),
            'ClientSecret' => Config::get('services.QUICKBOOKS_CLIENT_SECRET'),
            'RedirectURI' => Config::get('services.QUICKBOOKS_REDIRECT_URI'),
            'accessTokenKey' => Config::get('services.QUICKBOOKS_ACCESS_TOKEN'),
            'refreshTokenKey' => Config::get('services.QUICKBOOKS_REFRESH_TOKEN'),
            'QBORealmID' => Config::get('services.QUICKBOOKS_REALM_ID'),
            'baseUrl' => Config::get('services.QUICKBOOKS_BASE_URL'),
        ]);

        $this->clientId = 'ABBIl94z3LbVIcahjPl7CP5EpNcJMewnxymypNzI7izuqDlKQR';
        $this->clientSecret = 'OiZJBbgyXbiSPzBRALYUCIQevnMhpDft1YfH9StK';
        $this->accessToken = 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Sm9BUj2WfuooGq8ORrR6sw.ovkr57xKdg5VcPxR2H3j82_UvzVc2v1IrQQ-hGHCuFSp5roiRbo_vI3Ey_KZ86KxCOfSX94-5bY_YFPqo4FtLP9-dAvswGUXveuLkJxu7QdPl0XqdP0ocX6nGlNBhy64J3fyYwjKpAFuGzvyGknWrP1tmhBsC-mTk_pkMDJy1aMPqpUVnF2t7z09aOMwW_Vjm1OuZytlwvd5_abjK1I0v-Xw9TaBtvrYQkQVm0IvpPL1Fphksifi8f4VIunK7DqFHFNjEQOf6qrFHYYo2mCpoFH-QSoHW_w_6_RpaoXXwdpKH1QLhsStuE6N83zv6lxd9Zo-zLMDzW23f8135S5x8wRnMceM3yISHpCL81ENzE6aVrPfT_lHYz4bZAWutCmpe2naL5zkNz0TBGhrHEzyxODv39mJIp_ZuD-C3LLXOOu0kZy73nczoDdOecufBGfHL-GKynpkq2dvg6Mnjr7CRqFZO_mbiPzJsokcw-FJ7eQNBT7s1G42U15IMTgragyDbZU_5ZL5-1HY8R4XuaLyjZMIkF0sE1Lnusn_5SgLHcjtA3_0iZSmwaL6StzDeSP5r3MlV46NBcZa2l4cHq9DQoBV0ELeSsY6-ZrQNcvEQaRORezy0myqZFA0CY0VHKvKXXhqbXbPmYuXiMjeHJ7IC7XJD9krwlC_DTRqei6UBVVYqg4HmtxXLBO9MElknzJNzjTE_T7po89luO5ryDd_8kkV-qGl75W8v-ss7snOwyL_eZveQDbU1Uq0SKOJz-nLK25J21SoI72wr7qszbPq7KbSIDi4__jgbqvGJJnkrPt1zMXox5mkpMLvW3MUnrZAvLGCGpn1OwBNTdgEpm1IzYYcsxpSuK4R2-Rh71CD4vpm_mKyzsOAp2aAx2ZJU4-R.2ILf5qabVJ4UTfjOqqjGlg';
        $this->refreshToken = 'AB117105784132CXmhMmMfAqs2w7nEEqm8TyaRyuGElrdVdb4P';
    }

    /**
     * @param $request
     * @return 
     */
    public function accessToken($request)
    {
        /*$accessToken = new OAuth2AccessToken($this->clientId, $this->clientSecret, $this->accessToken, $this->refreshToken);
        $accessToken->setRealmId($qb->company_id->value);
        $accessToken->setBaseURL('development');*/

        // Prep Data Services
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => "ABBIl94z3LbVIcahjPl7CP5EpNcJMewnxymypNzI7izuqDlKQR",
            'ClientSecret' => "OiZJBbgyXbiSPzBRALYUCIQevnMhpDft1YfH9StK",
            'RedirectURI' => "https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl",
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "Development"
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        $authorizationUrl = $OAuth2LoginHelper->getAuthorizationCodeURL(); // This link has called into the browser 

        $resultURL = "https://developer.intuit.com/app/developer/playground?code=AB11701858084maEEMSU1byY9OMnnjzVKV8h6fCw2WpgsoiW0g&state=FMMGX&realmId=4620816365362100120";

        //$parseUrl = $this->parseAuthRedirectUrl(htmlspecialchars_decode($resultURL));

        $authorizationCode = "AB11701858084maEEMSU1byY9OMnnjzVKV8h6fCw2WpgsoiW0g";
        $realmId = "4620816365362100120";

        /*
        * Update the OAuth2Token
        */
        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($authorizationCode, $realmId);
        $dataService->updateOAuth2Token($e);

        /*
        * Setting the accessToken for session variable
        */
        return $_SESSION['sessionAccessToken'] = $accessToken;

    }

    function parseAuthRedirectUrl($url)
    {
        parse_str($url,$qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    /**
     * @param $request
     * @return 
     */
    public function refreshToken($request)
    {
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken(Config::get('services.QUICKBOOKS_REFRESH_TOKEN'));
        $error = $this->dataService->getLastError();
        
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
        
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            return [
                'accessTokenValue' => $accessTokenValue,
                'refreshTokenValue' => $refreshTokenValue
            ];
        }
    }

    /**
     * @param $request
     * @return 
     */
    public function invoiceListError($request)
    {
        /* 
        // Prep Data Services
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => "ABBIl94z3LbVIcahjPl7CP5EpNcJMewnxymypNzI7izuqDlKQR",
            'ClientSecret' => "OiZJBbgyXbiSPzBRALYUCIQevnMhpDft1YfH9StK",
            'RedirectURI' => "https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl",
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "Development"
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        

        $authorizationUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        //echo "<pre>"; print_r($authorizationUrl); exit;

        header("Location: ".$authorizationUrl);

        return $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken("authorizationCode", "RealmId");

        */

        // Prep Data Services
        /*$dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => "ABBIl94z3LbVIcahjPl7CP5EpNcJMewnxymypNzI7izuqDlKQR",
            'ClientSecret' => "OiZJBbgyXbiSPzBRALYUCIQevnMhpDft1YfH9StK",
            'accessTokenKey' => 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..rb7bb5qyYEsVUsFD-HoGZQ.fQoBj0JzUu9hGFL56ENffpWzST_PCes5IzZ8jO9LOjVvjeLs_JjcaUkDb72HwYJyGhNmcmPxjnPVrQg8YeowMbtDaq9no0pF2xX9dnktyoLQCs92s5XkF53G1qwh2d9mXUPf0vvGv1fLHCpuGe6eDi58jmlbM8IL0yr0ec1mnqgMe9-0ptyc62jKryTArzDRc7trY0olICtZo1O8Oam--ouzGhpkf1UUfYQ-PzLSbPu4Ma4J6fON-eP-MLc6maZ-tmwlrFZTvOC56G1iiPqQnntxPghrZSDPXYJ8t8U46gN8LtHSWZWz1aAhvh4Jrassy9WeHwr2Ud488fmmcljaEZyNlI5PI3NWgryhcLkKFhTvsE_9ryIl8WEDZ88Fnj0gotBIEeT7JAmIn_UK4eumqR3nLVKWogke8TxKMtzfEuV47xh5_rZBXiN2gm7O7zRzAARPgLP2xmQ3wYUapLaBxO1mPUS5iRa7GwrflqjuBQnooZoiXMNRG22K1LvX4_Q1bGUHUQL01hmGJM_cnWz2jetbIk5smBNoqrCuqgFTMZ13_98SPP2Ox6zGY-NBm0uaQ8EQUCvcU93Birg9kXefYhGbRoY0BsnkadAdk_f5zWuwqTDzidz2_dunPC-_73MPUns_2aJ0Xj60P_oLq-rj7tryzDGGwjWgJEfDYfDOeRh90b1c08KBE7OIWt0G29hDhUsKQYHzwPCBg4T8yLi2-JjLmHoRPHwvt7hoQ-YSSbiXsp4GipWB9trUyzzp4nxh.NTnU5BpgS5uoZ0aZEhGmOA',
            'refreshTokenKey' => "AB11710569802SOat7xWIP9pEaXwoLeUT5FkHOMG8FOeoWdZK9",
            'QBORealmId' => "4620816365362100120",
            'baseUrl' => "Development"
        ));*/

        $this->dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $this->dataService->throwExceptionOnError(true);
        //Add a new Invoice

        $invoice = $this->dataService->FindbyId('invoice', 1);
        $error = $this->dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            echo "Created Id={$invoice->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($invoice, $urlResource);
            echo $xmlBody . "\n";
        }

    }

    /**
     * @param $request
     * @return 
     */
    public function invoiceCreate($request)
    {
        $this->dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $this->dataService->throwExceptionOnError(true);
        //Add a new Invoice
        $theResourceObj = Invoice::create([
            "Line" => [
            [
                "Amount" => 100.00,
                "DetailType" => "SalesItemLineDetail",
                "SalesItemLineDetail" => [
                "ItemRef" => [
                    "value" => 1,
                    "name" => "Services"
                ]
                ]
            ]
            ],
            "CustomerRef"=> [
                "value"=> 1
            ],
            "BillEmail" => [
                "Address" => "Familiystore@intuit.com"
            ],
            "BillEmailCc" => [
                "Address" => "a@intuit.com"
            ],
            "BillEmailBcc" => [
                "Address" => "v@intuit.com"
            ]
        ]);

        try {
            $resultingObj = $this->dataService->Add($theResourceObj);
            $error = $this->dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            else {
                return $resultingObj;
            }
            
        } catch (ServiceException $ex) {
            return  "Error message: " . $ex->getMessage();
        }

    }

    /**
     * @param $request
     * @return 
     */
    public function invoiceShow($request)
    {
        $invoice = $this->dataService->FindbyId('invoice', 146);
        $error = $this->dataService->getLastError();
        
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            return $invoice;
        }
    }

    /**
     * @param $request
     * @return 
     */
    public function customerCreateError($request)
    {
        //$accessToken = new OAuth2AccessToken($clientID, $clientSecret, $accessTokenString, $refreshTokenString);
        //$accessToken->setRealmId($company_id);

        /*$result = $OAuth2LoginHelper->getUserInfo($accessToken->getAccessToken(), 'development');


        $accessToken->setRealmId();
        $CompanyInfo = $this->dataService->getCompanyInfo();*/
        /*$accessToken = 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Sm9BUj2WfuooGq8ORrR6sw.ovkr57xKdg5VcPxR2H3j82_UvzVc2v1IrQQ-hGHCuFSp5roiRbo_vI3Ey_KZ86KxCOfSX94-5bY_YFPqo4FtLP9-dAvswGUXveuLkJxu7QdPl0XqdP0ocX6nGlNBhy64J3fyYwjKpAFuGzvyGknWrP1tmhBsC-mTk_pkMDJy1aMPqpUVnF2t7z09aOMwW_Vjm1OuZytlwvd5_abjK1I0v-Xw9TaBtvrYQkQVm0IvpPL1Fphksifi8f4VIunK7DqFHFNjEQOf6qrFHYYo2mCpoFH-QSoHW_w_6_RpaoXXwdpKH1QLhsStuE6N83zv6lxd9Zo-zLMDzW23f8135S5x8wRnMceM3yISHpCL81ENzE6aVrPfT_lHYz4bZAWutCmpe2naL5zkNz0TBGhrHEzyxODv39mJIp_ZuD-C3LLXOOu0kZy73nczoDdOecufBGfHL-GKynpkq2dvg6Mnjr7CRqFZO_mbiPzJsokcw-FJ7eQNBT7s1G42U15IMTgragyDbZU_5ZL5-1HY8R4XuaLyjZMIkF0sE1Lnusn_5SgLHcjtA3_0iZSmwaL6StzDeSP5r3MlV46NBcZa2l4cHq9DQoBV0ELeSsY6-ZrQNcvEQaRORezy0myqZFA0CY0VHKvKXXhqbXbPmYuXiMjeHJ7IC7XJD9krwlC_DTRqei6UBVVYqg4HmtxXLBO9MElknzJNzjTE_T7po89luO5ryDd_8kkV-qGl75W8v-ss7snOwyL_eZveQDbU1Uq0SKOJz-nLK25J21SoI72wr7qszbPq7KbSIDi4__jgbqvGJJnkrPt1zMXox5mkpMLvW3MUnrZAvLGCGpn1OwBNTdgEpm1IzYYcsxpSuK4R2-Rh71CD4vpm_mKyzsOAp2aAx2ZJU4-R.2ILf5qabVJ4UTfjOqqjGlg';
        
        $this->dataService->updateOAuth2Token($accessToken);
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $result = $OAuth2LoginHelper->getUserInfo();

        dd($result); exit;
        $this->dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $this->dataService->throwExceptionOnError(true); */
        //Add a new Vendor
        $theResourceObj = Customer::create([
            "BillAddr" => [
                "Line1" => "123 Main Street",
                "City" => "Mountain View",
                "Country" => "USA",
                "CountrySubDivisionCode" => "CA",
                "PostalCode" => "94042"
            ],
            "Notes" => "Here are other details.",
            "Title" => "Mr",
            "GivenName" => "James",
            "MiddleName" => "B",
            "FamilyName" => "King",
            "Suffix" => "Jr",
            "FullyQualifiedName" => "King Groceries",
            "CompanyName" => "King Groceries",
            "DisplayName" => "King's Groceries Displayname",
            "PrimaryPhone" => [
                "FreeFormNumber" => "(555) 555-5555"
            ],
            "PrimaryEmailAddr" => [
                "Address" => "jdrew@myemail.com"
            ]
        ]);

        $resultingObj = $this->dataService->Add($theResourceObj);
        $error = $this->dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
            echo $xmlBody . "\n";
        }
     
 
    }

    /**
     * @param $request
     * @return 
     */
    public function customerCreate($request)
    {
        $displayname='Test'.' '.'user13';

        $customer = Customer::create([
            "GivenName" => $displayname,
            "DisplayName" =>  $displayname,
            "PrimaryEmailAddr" => [
                "Address" => 'test13@gmail.com'
            ],
            "BillAddr" => [
                "Line1" => "123 Main Street",
                "City" => "Mountain View",
                "Country" => "USA",
            ],
            "PrimaryPhone" => [
                "FreeFormNumber" => '+923007731712'
            ]
        ]);

        try {
            $result = $this->dataService->Add($customer);
            $error = $this->dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            else {
                return $result;
            }
            
        } catch (ServiceException $ex) {
            return  "Error message: " . $ex->getMessage();
        }
            
    }

    /**
     * @param $request
     * @return 
     */
    public function customerShow($request)
    {
        $customer = $this->dataService->FindbyId('customer', 58);
        $error = $this->dataService->getLastError();
        
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            //echo "Created Id={$customer->Id}. Reconstructed response body:\n\n";
            return $customer;
            //$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($customer, $urlResource);
            //return $xmlBody . "\n";
        }
    }
}