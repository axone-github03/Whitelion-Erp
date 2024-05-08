<?php

namespace App\Exports;

use App\Models\SalePerson;
use App\Models\LeadQuestionAnswer;
use App\Models\LeadQuestionOptions;
use App\Models\LeadStatusUpdate;
use App\Models\ChannelPartner;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalesPersonHierarchy implements FromCollection
{

    public function collection()
    {
        $lstleadData = array();

        $objNewItemHeader['sales_person_id'] = 'Sales Person Id';
        $objNewItemHeader['sales_person_name'] = 'Sales Person Name';
        $objNewItemHeader['sales_person_contact_number'] = 'Sales Person Phone Number';
        $objNewItemHeader['child_sales_person_id'] = 'Child Sales Person Id';
        $objNewItemHeader['child_sales_person_name'] = 'Child Sales Person Name';
        $objNewItemHeader['child_sales_person_contact_number'] = 'Child Sales Person Phone Number';
        $objNewItemHeader['child_sales_person_reporting_to'] = 'Child Sales Person Reporting To';
        array_push($lstleadData,$objNewItemHeader);
        
        $column = array(
            'sale_person.user_id',
            'users.phone_number'
        );
        $Query = SalePerson::query();
        $Query->select($column);
        $Query->selectRaw('CONCAT(users.first_name," ",users.last_name) AS sales_person_name');
        $Query->leftJoin('users', 'users.id', '=', 'sale_person.user_id');
        $dataList = $Query->get();

        foreach ($dataList as $key => $value) {
            $sales_person_id = $value['user_id'];
            $sales_person_name = $value['sales_person_name'];
            $sales_person_contact_number = $value['phone_number'];
            
            $Childcolumn = array(
                'sale_person.user_id',
                'users.phone_number'
            );
            $Child_Query = SalePerson::query();
            $Child_Query->select($Childcolumn);
            $Child_Query->selectRaw('CONCAT(users.first_name," ",users.last_name) AS sales_person_name');
            $Child_Query->selectRaw('CONCAT(reporting.first_name," ",reporting.last_name) AS child_sales_person_reporting_name');
            $Child_Query->leftJoin('users', 'users.id', '=', 'sale_person.user_id');
            $Child_Query->leftJoin('users as reporting', 'reporting.id', '=', 'sale_person.reporting_manager_id');
            $Child_Query->whereIn('sale_person.user_id',getChildSalePersonsIds($value['user_id']) );
            $childDataList = $Child_Query->get();
            if($childDataList){
                foreach ($childDataList as $child_key => $child_value) {
                    $objNewItem['sales_person_id'] = $sales_person_id;
                    $objNewItem['sales_person_name'] = $sales_person_name;
                    $objNewItem['sales_person_contact_number'] = $sales_person_contact_number;
                    $objNewItem['child_sales_person_id'] = $child_value['user_id'];
                    $objNewItem['child_sales_person_name'] = $child_value['sales_person_name'];
                    $objNewItem['child_sales_person_contact_number'] = $child_value['phone_number'];
                    $objNewItem['child_sales_person_reporting_to'] = $child_value['child_sales_person_reporting_name'];
                    array_push($lstleadData,$objNewItem);
                }
                
            }else {
                $objNewItem['sales_person_id'] = $sales_person_id;
                $objNewItem['sales_person_name'] = $sales_person_name;
                $objNewItem['sales_person_contact_number'] = $sales_person_contact_number;
                $objNewItem['child_sales_person_id'] = '';
                $objNewItem['child_sales_person_name'] = '';
                $objNewItem['child_sales_person_contact_number'] = '';
                $objNewItem['child_sales_person_reporting_to'] = '';
                array_push($lstleadData,$objNewItem);
            }
		}
        return collect($lstleadData);
    }
}
