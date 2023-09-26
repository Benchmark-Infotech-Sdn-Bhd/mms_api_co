<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\XeroSettings;

class XeroSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        XeroSettings::create(
            [
                'id' => 1, 
                'title' => 'XERO', 
                'url' => 'https://api.xero.com/api.xro/2.0/', 
                'client_id' => '05384CFA1A624054B05E572976EB3748', 
                'client_secret' => '8CB02fkMqeGwOf6HGG1HJ3cB-wMhEPpzYd2-fuMwW72GuUBJ', 
                'tenant_id' => '08e3e7d9-5304-4fa6-a337-1f21262b6dca', 
                'access_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjFDQUY4RTY2NzcyRDZEQzAyOEQ2NzI2RkQwMjYxNTgxNTcwRUZDMTkiLCJ0eXAiOiJKV1QiLCJ4NXQiOiJISy1PWm5jdGJjQW8xbkp2MENZVmdWY09fQmsifQ.eyJuYmYiOjE2OTQ4MTMwMTYsImV4cCI6MTY5NDgxNDgxNiwiaXNzIjoiaHR0cHM6Ly9pZGVudGl0eS54ZXJvLmNvbSIsImF1ZCI6Imh0dHBzOi8vaWRlbnRpdHkueGVyby5jb20vcmVzb3VyY2VzIiwiY2xpZW50X2lkIjoiMDUzODRDRkExQTYyNDA1NEIwNUU1NzI5NzZFQjM3NDgiLCJzdWIiOiI3ODM5NjUyYWNjM2I1ZjJlYjFiZTNlMWM1YTdlMmZmNSIsImF1dGhfdGltZSI6MTY5NDgxMTA4NiwieGVyb191c2VyaWQiOiJhZmIyMjMwYy1kMjRmLTQ0NGQtOGIwOC05OTc0YzMxMjkxNTkiLCJnbG9iYWxfc2Vzc2lvbl9pZCI6ImU1YzM3Yjg0NzZjMTQ0ZmU4NjE0Y2JkMmEwN2IwM2Q2Iiwic2lkIjoiZTVjMzdiODQ3NmMxNDRmZTg2MTRjYmQyYTA3YjAzZDYiLCJqdGkiOiJBNDQzQzE3RjA1RENFMkJCMUEwN0M0RjFFNDExRDlERCIsImF1dGhlbnRpY2F0aW9uX2V2ZW50X2lkIjoiZTlhYzJkMTUtOTViZi00MjMxLWE1NjktZTVmOWRlYWZlZTc1Iiwic2NvcGUiOlsiYWNjb3VudGluZy5zZXR0aW5ncyIsImFjY291bnRpbmcudHJhbnNhY3Rpb25zIiwiYWNjb3VudGluZy5jb250YWN0cyIsIm9mZmxpbmVfYWNjZXNzIl0sImFtciI6WyJwd2QiXX0.X4HJ4rv7LZ6ytBCyWubo5AJ5GlnQyV7VJ34sSRrvR_wjLLB2R3PBbU8HcarVfJxCHWLZZXACeAGZ-jDiKmeLk65h1Q5g2wAdDC84v5PTEPAOZgXely93uZy69Wqk8M_qUhCHvOyAQtKslezZgxUiQwKkAc8kxy8--jJDUIKKLOVXvPC7Y79CPiPl-RYaJZXcsPj9q-oiGwWrdm2LbE6811q56l8O__DVXKCSd7LrSMXM_8sZCbGFuHK5svTPAFPr-AFh6JkAJC_b4JjTp_uCLp1VZjPF-qi_D1LrUTvG3ZFlcspfHO8KqSk4Bgocqbxtc5vWafrJEsyMK4oaBLm4YQ', 
                'refresh_token' => 'SsQvReffD2oayN446gln08JlVVlJEWrWnki0RyS3O6k', 
                'created_by' => '1', 
                'modified_by' => '1',                
            ]
        );
    }
}
