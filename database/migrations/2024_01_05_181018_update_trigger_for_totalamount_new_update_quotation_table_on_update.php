<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTriggerForTotalamountNewUpdateQuotationTableOnUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        DROP TRIGGER IF EXISTS `update_quotation_table_on_update`;CREATE DEFINER=`root`@`localhost` TRIGGER `update_quotation_table_on_update` AFTER UPDATE ON `wltrn_quot_itemdetails` FOR EACH ROW BEGIN
  DECLARE total_igst_amount DECIMAL(10, 2) DEFAULT 0;
  DECLARE total_cgst_amount DECIMAL(10, 2) DEFAULT 0;
  DECLARE total_sgst_amount DECIMAL(10, 2) DEFAULT 0;
  DECLARE total_net_amount DECIMAL(10, 2) DEFAULT 0;
  DECLARE total_quot_whitelion_amount DECIMAL(10, 2) DEFAULT 0;
  DECLARE total_quot_billing_amount DECIMAL(10, 2) DEFAULT 0;
  DECLARE total_quot_other_amount DECIMAL(10, 2) DEFAULT 0;

  -- Calculate Whitelion Amount For New Row
  SELECT
  IFNULL(SUM(wltrn_quot_itemdetails.net_amount), 0)
  INTO
    total_quot_whitelion_amount
  FROM
    wltrn_quot_itemdetails
  WHERE
    quot_id = NEW.quot_id
    AND 
    itemgroup_id IN (1,3)
    AND wltrn_quot_itemdetails.isactiveroom = 1
    AND wltrn_quot_itemdetails.isactiveboard = 1;
    
-- Calculate Billing Amount For New Row
  SELECT
  IFNULL(SUM((wltrn_quot_itemdetails.net_amount-(wltrn_quot_itemdetails.net_amount*wlmst_item_prices.channel_partners_discount/100))), 0)
  INTO
    total_quot_billing_amount
  FROM
    wltrn_quot_itemdetails
  LEFT JOIN wlmst_item_prices ON wlmst_item_prices.id = wltrn_quot_itemdetails.item_price_id
  WHERE
    wltrn_quot_itemdetails.quot_id = NEW.quot_id
    AND 
    wltrn_quot_itemdetails.itemgroup_id IN (1,3)
    AND wltrn_quot_itemdetails.isactiveroom = 1
    AND wltrn_quot_itemdetails.isactiveboard = 1;
    
-- Calculate Other Amount For New Row
  SELECT
  IFNULL(SUM(wltrn_quot_itemdetails.net_amount), 0)
  INTO
    total_quot_other_amount
  FROM
    wltrn_quot_itemdetails
  WHERE
    quot_id = NEW.quot_id
    AND 
    itemgroup_id NOT IN (1,3)
    AND wltrn_quot_itemdetails.isactiveroom = 1
    AND wltrn_quot_itemdetails.isactiveboard = 1;

  -- Calculate the sum for the new row
  SELECT
  IFNULL(SUM(igst_amount), 0),
  IFNULL(SUM(cgst_amount), 0),
  IFNULL(SUM(sgst_amount), 0),
  IFNULL(SUM(net_amount), 0)
  INTO
    total_igst_amount,
    total_cgst_amount,
    total_sgst_amount,
    total_net_amount
  FROM
    wltrn_quot_itemdetails
  WHERE
    quot_id = NEW.quot_id
    AND wltrn_quot_itemdetails.isactiveroom = 1
    AND wltrn_quot_itemdetails.isactiveboard = 1;
    
  -- Update the order_table with the new totals
  UPDATE
    wltrn_quotation
  SET
    igst_amount = total_igst_amount,
    cgst_amount = total_cgst_amount,
    sgst_amount = total_sgst_amount,
    net_amount = total_net_amount,
    quot_total_amount = total_net_amount,
    quot_whitelion_amount = total_quot_whitelion_amount,
    quot_billing_amount = total_quot_billing_amount,
    quot_other_amount = total_quot_other_amount
  WHERE
    id = NEW.quot_id;
END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER `update_quotation_table_on_update`');
    }
}
