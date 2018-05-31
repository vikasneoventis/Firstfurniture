<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    protected $_coreDate = null;
    protected $_storeId = 0;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Wyomind\DataFeedManager\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
    ) {
    
        $this->_coreDate = $coreDate;
        $this->_storeId = $storeCollectionFactory->create()->getFirstStoreId();
    }

    /**
     * @version 8.0.0
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    

        unset($context);

        $installer = $setup;
        $installer->startSetup();

        $data = ["template" => [], "functions" => [], "variables" => []];
        $data['templates'][] = [
            "id" => null,
            "name" => "GoogleShopping",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => "<item>
<!-- Basic Product Information -->
<g:id>{{product.sku}}</g:id>
<title>{{product.name}}</title>
<link>{{parent.url | product.url}}</link>
<description>{{parent.description php=\"strip_tags(\$self)\" | product.description php=\"strip_tags(\$self)\"}}</description>
<g:google_product_category>{{product.google_product_category | parent.google_product_category}}</g:google_product_category>

<g:product_type>{{parent.categories nth=0 | product.categories nth=0 }}</g:product_type>
<g:product_type>{{parent.categories nth=1 | product.categories nth=1 }}</g:product_type>
<g:product_type>{{parent.categories nth=2 | product.categories nth=2 }}</g:product_type>
<g:product_type>{{parent.categories nth=3 | product.categories nth=3 }}</g:product_type>
<g:product_type>{{parent.categories nth=4 | product.categories nth=4 }}</g:product_type>
<g:product_type>{{parent.categories nth=5 | product.categories nth=5 }}</g:product_type>
<g:product_type>{{parent.categories nth=6 | product.categories nth=6 }}</g:product_type>
<g:product_type>{{parent.categories nth=7 | product.categories nth=7 }}</g:product_type>
<g:product_type>{{parent.categories nth=8 | product.categories nth=8 }}</g:product_type>
<g:product_type>{{parent.categories nth=9 | product.categories nth=9 }}</g:product_type>

<g:image_link>{{parent.image_link index=\"0\"| product.image_link index=\"0\"}}</g:image_link>
<g:additional_image_link>{{parent.image_link index=\"1\" | product.image_link index=\"1\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"2\" | product.image_link index=\"2\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"3\" | product.image_link index=\"3\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"4\" | product.image_link index=\"4\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"5\" | product.image_link index=\"5\"}}</g:additional_image_link>

<!-- Availability & Price -->
<g:availability>{{product.availability}}</g:availability>

<g:price>{{product.price currency=USD vat_rate=0 suffix=\" USD\"}}</g:price>
<g:sale_price>{{product.sale_price currency=USD vat_rate=0 suffix=\" USD\"}}</g:sale_price>
<g:sale_price_effective_date>{{product.sale_price_effective_date}}</g:sale_price_effective_date>

<g:condition>{{product.condition}}</g:condition>
<!-- Unique Product Identifiers-->
<g:brand>{{product.brand}}</g:brand>
<g:gtin>{{product.upc}}</g:gtin>
<g:mpn>{{product.mpn}}</g:mpn>
<g:identifier_exists>TRUE</g:identifier_exists>

<!-- Apparel Products -->
<g:gender>{{product.gender}}</g:gender>
<g:age_group>{{product.age_group}}</g:age_group>
<g:color>{{product.color}}</g:color>
<g:size>{{product.size}}</g:size>


<!-- Product Variants -->
<g:item_group_id>{{parent.sku}}</g:item_group_id>
<g:material>{{product.material}}</g:material>
<g:pattern>{{product.pattern}}</g:pattern>

<!-- Shipping -->
<g:shipping_weight>{{product.weight php=\"float(\$self,2)\" suffix=\"kg\"}}</g:shipping_weight>

<!-- AdWords attributes -->
<g:custom_label_0>{{product.custom_label_0}}</g:custom_label_0>
<g:custom_label_1>{{product.custom_label_1}}</g:custom_label_1>
<g:custom_label_2>{{product.custom_label_2}}</g:custom_label_2>
<g:custom_label_3>{{product.custom_label_3}}</g:custom_label_3>
<g:custom_label_4>{{product.custom_label_4}}</g:custom_label_4>
</item>",
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,grouped,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => "[]",
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">  
<channel>  
<title>Data feed Title</title>
<link>http://www.website.com</link>
<description>Data feed description.</description>",
            "footer" => "</channel>
</rss>",
            "encoding" => "UTF-8",
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "LeGuide",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<product place="{{product.inc}}">
   <categorie>{{product.categories}}</categorie> 
   <identifiant_unique>{{product.sku}}</identifiant_unique>
   <titre>{{product.meta_title}}</titre>
   <prix currency="EUR">{{product.price currency="EUR"}}</prix>
   <url_produit>{{product.url}}</url_produit>
   <url_image>{{parent.image_link index="0"}}</url_image>
   <description>{{product.short_description}}</description>
   <frais_de_livraison>90</frais_de_livraison>
   <D3E>0</D3E>
   <disponibilite>{{product.is_in_stock out_of_stock=0 in_stock=1}} </disponibilite>
   <delai_de_livraison>5 jours</delai_de_livraison>
   <?php if("{{product.has_special_price}}") return "<prix_barre currency=\\"EUR\\">{{product.normal_price}}</prix_barre>"; ?>
   <type_promotion><?php if("{{product.has_special_price}}") return "1"; else return "0"; ?></type_promotion>
   <occasion>0</occasion>
   <devise>EUR</devise>
</product>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,grouped,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => "[]",
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<catalogue lang="FR" >',
            "footer" => "</catalogue>",
            "encoding" => "UTF-8",
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Twenga",
            "type" => 3,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.price}}", "{{product.url}}", "{{product.meta_title}}", "{{product.categories}}", "{{parent.image_link index=0}}", "{{product.short_description}}", "{{product.sku}}", "{{product.is_in_stock in_stock=Y out_of_stock=N pre_order=N}}", "{{product.qty}}", "1", "0"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,grouped,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => "[]",
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["price", "product_url", "designation", "category", "image_url", "description", "merchant_id", "in_stock", "Stock_detail", "product_type", "condition"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => ';',
            "field_protector" => '"',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Kelkoo",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<product>
   <id>{{product.sku}}</id>
   <model>{{product.meta_title}}</model>
   <description>{{product.short_description php="dfm_substr($self,180,\'\')"}</description>
   <price>{{product.price}}</price>
   <url>{{product.url}}</url>
   <merchantcat>{{product.categories}}</merchantcat>
   <image>{{parent.image_link index="0"}}</image>
   <used>neuf</used>
   <availability>{{product.is_in_stock in_stock=1 out_of_stock=4 pre_order=4}}</availability>
   <deliveryprice>90.00</deliveryprice>
   <deliverytime>Sous 5 jours</deliverytime>
   <pricenorebate>{{product.normal_price}}</pricenorebate>
   <percentagepromo><?php 
   if ("{{product.has_special_price}}") 
    return round(100-("{{product.special_price}}"*100/"{{product.normal_price}}") ); 
   else
    return 0; 
   ?></percentagepromo>
   <promostart><?php return date("Y-m-d",time()); ?></promostart>
   <promoend><?php return date("Y-m-d",time()+604800); ?></promoend>
</product>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,grouped,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => "[]",
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="ISO-8859-1"?>
<products>',
            "footer" => "</products>",
            "encoding" => "UTF-8",
            "field_separator" => ';',
            "field_protector" => '"',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Shopping.com",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<Product>
    <Merchant_SKU>{{product.sku}}</Merchant_SKU>		
    <MPN></MPN>		
    <UPC></UPC>		
    <EAN></EAN>		
    <ISBN></ISBN>		
    <Manufacturer>{{product.manufacturer}}</Manufacturer>		
    <Product_Name>{{product.name}}</Product_Name>		
    <Product_URL>{{product.url}}</Product_URL> 		
    <Mobile_URL></Mobile_URL> 		
    <Current_Price>{{product.price}}</Current_Price> 		
    <Original_Price>{{product.normal_price}}</Original_Price> 		
    <Category_ID></Category_ID>		
    <Category_Name>{{product.categories}}</Category_Name>		
    <Sub-category_Name></Sub-category_Name>		
    <Parent_SKU></Parent_SKU>		
    <Parent_Name></Parent_Name>		
    <Product_Description>{{product.short_description}}</Product_Description>		
    <Stock_Description></Stock_Description>		
    <Product_Bullet_Point_1></Product_Bullet_Point_1>		
    <Product_Bullet_Point_2></Product_Bullet_Point_2>		
    <Product_Bullet_Point_3></Product_Bullet_Point_3>		
    <Product_Bullet_Point_4></Product_Bullet_Point_4>		
    <Product_Bullet_Point_5></Product_Bullet_Point_5>		
    <Image_URL>{{parent.image_link index="0"}}</Image_URL>		
    <Alternative_Image_URL_1>{{parent.image_link index="1"}}</Alternative_Image_URL_1>		

    <Product_Type></Product_Type>		
    <Style></Style>		
    <Condition>Neuf</Condition>		
    <Gender></Gender>		
    <Department></Department>		
    <Age_Range></Age_Range>		
    <Color>Noir/Blanc</Color>		
    <Material></Material>		
    <Format></Format>		
    <Team></Team>		
    <League></League>		
    <Fan_Gear_Type></Fan_Gear_Type>		
    <Software_Platform></Software_Platform>		
    <Software_Type></Software_Type>		
    <Watch_Display_Type></Watch_Display_Type>		
    <Cell_Phone_Type></Cell_Phone_Type>		
    <Cell_Phone_Service_Provider></Cell_Phone_Service_Provider>		
    <Cell_Phone_Plan_Type></Cell_Phone_Plan_Type>		
    <Usage_Profile></Usage_Profile>		
    <Size></Size>		
    <Size_Unit_of_Measure></Size_Unit_of_Measure>		
    <Product_Length></Product_Length>		
    <Length_Unit_of_Measure></Length_Unit_of_Measure>		
    <Product_Width></Product_Width >		
    <Width_Unit_of_Measure></Width_Unit_of_Measure>		
    <Product_Height></Product_Height>		
    <Height_Unit_of_Measure></Height_Unit_of_Measure>		
    <Product_Weight></Product_Weight>		
    <Weight_Unit_of_Measure></Weight_Unit_of_Measure>		
    <Unit_Price></Unit_Price>		
    <Top_Seller_Rank></Top_Seller_Rank>		
    <Product_Launch_Date></Product_Launch_Date>		
    <Stock_Availability></Stock_Availability>		
    <Shipping_Rate></Shipping_Rate>		
    <Shipping_Weight></Shipping_Weight>		
    <Estimated_Ship_Date></Estimated_Ship_Date>		
    <Coupon_Code></Coupon_Code>		
    <Coupon_Code_Description></Coupon_Code_Description>		
    <Merchandising_Type></Merchandising_Type>		
    <Bundle>Non</Bundle>		
    <Related_Products></Related_Products>		
</Product>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,grouped,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => "[]",
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="ISO-8859-1"?>
<Products>',
            "footer" => "</Products>",
            "encoding" => "UTF-8",
            "field_separator" => ';',
            "field_protector" => '"',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];


        $data['templates'][] = [
            "id" => null,
            "name" => "BingShopping",
            "type" => 2,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.id}}","{{product.name}}","{{parent.url php=\\"dfm_substr($self,100,\'\')\\"}}","{{product.price}} USD","{{parent.description php=\\"html_entity_decode(strip_tags($self))\\"}} ","{{parent.image_link index="0"}}","{{product.manufacturer}}","{{product.sku}}","{{product.sku}}","in stock","{{product.categories}}","{{weight php=\\"float($self,2)\\"}} kilograms","new","{{product.category_mapping}}"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"price","condition":"gt","value":"0"},{"line":"1","checked":true,"code":"name","condition":"notnull","value":""}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["MerchantProductID","Title","ProductURL","Price","Description","ImageURL","Brand","MPN","SKU","Availability","MerchantCategory","ShippingWeight","Condition","B_Category "]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '\t',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Shopzilla",
            "type" => 2,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.category_mapping}}","{{product.manufacturer}}","{{product.name}}","{{description php=\\"html_entity_decode(strip_tags($self))\\"}}","{{product.url}}","{{parent.image_link}}","{{product.sku}}","{{product.is_in_stock}}","new","{{product.weight}}","0","","","{{product.ean}}","{{product.price}}"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""},{"line":"1","checked":true,"code":"name","condition":"notnull","value":""},{"line":"2","checked":true,"code":"description","condition":"notnull","value":""},{"line":"3","checked":true,"code":"price","condition":"gt","value":"0"}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["Category ID", "Manufacturer", "Title", "Description", "Product URL", "Image URL", "SKU", "Availability", "Condition", "Ship Weight", "Ship Cost","Bid", "Promotional Code", "UPC", "Price"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '\t',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "PriceGrabber",
            "type" => 2,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.name}}", "{{product.mpn}}", "{{product.upc}}", "{{product.sku}}", "{{product.category_mapping}}", "{{product.name}}", "{{product.price}}", "{{product.is_in_stock in_stock=\\"Yes\\" out_of_stock=\\"No\\" pre_order=\\"No\\"}}", "{{product.url}}", "{{parent.image_link index="0"}}", "new", "0", "{{product.weight}}"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""},{"line":"1","checked":true,"code":"name","condition":"notnull","value":""},{"line":"2","checked":true,"code":"description","condition":"notnull","value":""},{"line":"3","checked":true,"code":"price","condition":"gt","value":"0"}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["Product Name", "Manufacturer Part Number (MPN)", "UPC", "Unique Retailer SKU", "Categorization", "Detailed Description", "Selling Price", "Availability", "Product URL", "Image URL", "Product Condition", "Shipping Costs", "Weight"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '|',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Nextag",
            "type" => 2,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.name}}", "{{product.manufacturer}}", "{{product.mpn}}", "{{product.upc}}", "{{product.sku}}", "{{product.categories}}", "{{product.short_description}}", "{{product.price}}", "{{product.is_in_stock in_stock=\\"Yes\\" out_of_stock=\\"No\\" pre_order=\\"No\\"}}", "{{product.url}}", "{{parent.image_link index="0"}}", "new", "0.00", "{{product.weight}}"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""},{"line":"1","checked":true,"code":"name","condition":"notnull","value":""},{"line":"2","checked":true,"code":"description","condition":"notnull","value":""},{"line":"3","checked":true,"code":"price","condition":"gt","value":"0"}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["Product Name", "Manufacturer", "Manufacturer Part #", "UPC", "Seller Part #", "Category", "Description", "Price", "Stock Status", "Click-Out URL", "Image URL", "Condition", "Ground Shipping", "Weight"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '|',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "AmazonProducts",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<Message>
    <MessageID>{{product.inc}}</MessageID>
    <OperationType>Update</OperationType>
    <Product>
        <SKU>{{product.sku}}</SKU>
        <StandardProductID>
            <Type>ASIN</Type>
            <Value>{{product.ean}}</Value>
        </StandardProductID>
        <ProductTaxCode>A_GEN_NOTAX</ProductTaxCode>
        <DescriptionData>
            <Title>{{product.name}}</Title>
            <Brand>{{product.manufacturer}}</Brand>
            <Description>{{product.description}}</Description>
            <ShippingWeight unitOfMeasure="KG">{{product.weight}}</ShippingWeight>
        </DescriptionData>
        <ProductData>
            <Home>
                <ProductType>
                    <Home>
                        <VariationData>
                            <VariationTheme>Size</VariationTheme>
                        </VariationData>
                    </Home>
                </ProductType>
                <Parentage>child</Parentage>
            </Home>
        </ProductData>
    </Product>
</Message>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""},{"line":"1","checked":true,"code":"name","condition":"notnull","value":""}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="utf-8"?>
<AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>YOUR_MERCHANT_ID_HERE</MerchantIdentifier>
</Header>
<MessageType>Product</MessageType>
<PurgeAndReplace>false</PurgeAndReplace>',
            "footer" => "</AmazonEnvelope>",
            "encoding" => "UTF-8",
            "field_separator" => '|',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "AmazonPrice",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<Message>
    <MessageID>{{product.inc}}</MessageID>
    <Price>
        <SKU>{{product.sku}}</SKU>
        <StandardPrice currency="USD">{{product.price currency=USD}}</StandardPrice>
    </Price>
</Message>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="utf-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
<Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>YOUR_MERCHANT_ID_HERE</MerchantIdentifier>
</Header>
<MessageType>Price</MessageType>',
            "footer" => "</AmazonEnvelope>",
            "encoding" => "UTF-8",
            "field_separator" => '|',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "AmazonInventory",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<Message>
        <MessageID>{{product.inc}}</MessageID>
        <Inventory>
            <SKU>{{product.sku}}</SKU>
            <Quantity>{{product.qty}}</Quantity>
        </Inventory>
    </Message>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="utf-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
    <Header>
        <DocumentVersion>1.01</DocumentVersion>
        <MerchantIdentifier>YOUR_MERCHANT_ID_HERE</MerchantIdentifier>
    </Header>
    <MessageType>Inventory</MessageType>',
            "footer" => "</AmazonEnvelope>",
            "encoding" => "UTF-8",
            "field_separator" => '|',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "AmazonImage",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<Message>
            <MessageID>{{product.inc}}</MessageID>
            <ProductImage>
                <SKU>{{product.sku}}</SKU>
                <ImageType>Main</ImageType>
                <ImageLocation>{{parent.image_link}}</ImageLocation>
            </ProductImage>
        </Message>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"sku","condition":"notnull","value":""}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="utf-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
       <Header>
        <DocumentVersion>1.01</DocumentVersion>
        <MerchantIdentifier>YOUR_MERCHANT_ID_HERE</MerchantIdentifier>
    </Header>
    <MessageType>ProductImage</MessageType>',
            "footer" => "</AmazonEnvelope>",
            "encoding" => "UTF-8",
            "field_separator" => '|',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];


        $data['templates'][] = [
            "id" => null,
            "name" => "amazonAds",
            "type" => 2,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.category_mapping}}","{{product.name}}","{{parent.url}}","{{product.sku}}","{{product.price}}","{{product.brand}}","men, women","{{product.upc}}","{{parent.image_link index="0"}}","{{product.description php=\\"html_entity_decode(strip_tags(inline($self)))\\"}}","{{product.manufacturer}}","{{product.sku}}","","{{product.color}}","{{product.weight}}","{{product.size}}"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"name","condition":"notnull","value":""},{"line":"1","checked":true,"code":"price","condition":"gt","value":"0"}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["Category","Title","Link","SKU","Price","Brand","Department","UPC","Image","Description","Manufacturer","Mfr part number","Age","Color","Shipping Weight","Size"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '\t',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Ebay",
            "type" => 2,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["Add","103440","{{product.name}}","{{product.description php=\\"html_entity_decode(strip_tags(inline($self)))\\"}}","1000","{{parent.image_link index="0"}}","{{product.qty}}","FixedPrice","{{product.price}}","{{product.price}}","GTC","1","USA KOi, http://yourwebsite.com","None","1","contact@website.com","","","","","","","","","","","","","","5","","ReturnAccepted",""]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable",
            "category_type" => 0,
            "visibilities" => "2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[{"line":"0","checked":true,"code":"name","condition":"notnull","value":""},{"line":"1","checked":true,"code":"price","condition":"gt","value":"0"}]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["*Action(SiteID=France|Country=FR|Currency=EUR|Version=403|CC=ISO-8859-1)","*Category","*Title","Description","*ConditionID","PicURL","*Quantity","*Format","*StartPrice","BuyItNowPrice","*Duration","ImmediatePayRequired","*Location"," GalleryType","PayPalAccepted","PayPalEmailAddress","PaymentInstructions","DomesticInsuranceOption","DomesticInsuranceFee","InternationalInsuranceOption","InternationalInsuranceFee","StoreCategory","ShippingDiscountProfileID"," ShippingService-1:Option","ShippingService-1:Cost","ShippingService-1:Priority","ShippingService-2:Option","ShippingService-2:Cost","ShippingService-2:Priority"," *DispatchTimeMax","CustomLabel"," *ReturnsAcceptedOption","AdditionalDetails"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '\t',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Beslist",
            "type" => 1,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '<item>
    <titel>{{product.name}}</titel>
    <prijs>{{product.price}}</prijs>
    <url>{{product.url}}</url>
    <url_productplaatje>{{parent.image_link}}</url_productplaatje>
    <sku>{{product.sku}}</sku>
    <beschrijving>{{product.description}}</beschrijving>
</item>',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '<?xml version="1.0" encoding="UTF-8"?>
<feed>',
            "footer" => "</feed>",
            "encoding" => "UTF-8",
            "field_separator" => '\t',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Idealo",
            "type" => 3,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.sku}}","{{product.ean}}","{{product.mpn}}","{{product.manufacturer}}","{{product.name}}","{{product.description php=\\"html_entity_decode(strip_tags(inline(cleaner($self))))\\"}}","{{product.categories}}","{{product.price currency=\\"GBP\\" vat_rate=\\"GB\\"}}","Available Immediatly","{{parent.url}}","{{parent.image_link}}",""]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["Article number","EAN (European article number)","Manufacturers code / number","Manufacturer","Product Name","Description","Product Group","Price GBP","Delivery status","Product URL","Picture URL","Delivery Costs"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => '\t',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Yahoo",
            "type" => 3,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.categories nth=1 from=1 length=1}}", "{{product.url_key}}", "{{product.name}}", "{{product.sku}}", "{{product.price currency=USD vat_rate=0}}", "{{product.special_price currency=USD vat_rate=0}}", "{{product.short_description}}", "{{product.short_description}}", "{{product.description}}", "{{product.weight php=\\"float($self,2)\\"}}", "yes", "no", "", "{{product.is_in_stock}}", "{{product.name}}", "{{product.short_description}} {{product.description}}", "{{parent.url}}", "{{product.condition}}"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["path", "id", "name", "code", "price", "sale-price", "headline", "caption", "abstract", "ship-weight", "orderable", "taxable", "gift-certificate", "availability", "page-title", "description", "product-url", "condition"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => ';',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['templates'][] = [
            "id" => null,
            "name" => "Trovaprezzi",
            "type" => 3,
            "path" => "/feeds/",
            "status" => 1,
            "updated_at" => $this->_coreDate->date('Y-m-d H:i:s'),
            "store_id" => $this->_storeId,
            "product_pattern" => '{"product":["{{product.name}}","{{product.brand}}","{{product.description php=\\"inline($self)\\"}}","{{product.price currency=EUR vat_rate=IT}}","{{product.sku}}","{{parent.url}}","{{product.is_in_stock}}","{{product.categories nth=-1}}","{{parent.image_link index="0"}}","","{{product.mpn}}","{{product.ean}}<endrecord>"]}',
            "category_filter" => 1,
            "categories" => "*",
            "type_ids" => "simple,configurable,bundle,virtual,downloadable",
            "category_type" => 0,
            "visibilities" => "1,2,3,4",
            "attribute_sets" => "*",
            "attributes" => '[]',
            "cron_expr" => '{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
            "include_header" => 0,
            "header" => '{"header":["path", "id", "name", "code", "price", "sale-price", "headline", "caption", "abstract", "ship-weight", "orderable", "taxable", "gift-certificate", "availability", "page-title", "description", "product-url", "condition"]}',
            "footer" => "",
            "encoding" => "UTF-8",
            "field_separator" => ';',
            "field_protector" => '',
            "enclose_data" => 1,
            "clean_data" => 1,
            "dateformat" => "{f}",
            "ftp_enabled" => 0,
            "use_sftp" => 0,
            "ftp_active" => 0
        ];

        $data['functions'] = [
            [
                "id" => null,
                "script" => '<?php function float($self,$dec) { return number_format((float)$self,$dec,".",""); } ?>'
            ],
            [
                "id" => null,
                "script" => "<?php function cleaner(\$self) {\$value_cleaned = preg_replace('/' . '[\\x00-\\x1F\\x7F]' . '|[\\x00-\\x7F][\\x80-\\xBF]+' . '|([\\xC0\\xC1]|[\\xF0-\\xFF])[\\x80-\\xBF]*' . '|[\\xC2-\\xDF]((?![\\x80-\\xBF])|[\\x80-\\xBF]{2,})' . '|[\\xE0-\\xEF](([\\x80-\\xBF](?![\\x80-\\xBF]))|' . '(?![\\x80-\\xBF]{2})|[\\x80-\\xBF]{3,})' . '/S', ' ', \$self); \$value = str_replace('&#153;', '', \$value_cleaned); return \$value; } ?>"
            ],
            [
                "id" => null,
                "script" => "<?php function dfm_strtoupper(\$self) {\nreturn mb_strtoupper(\$self, \"UTF8\");\n}\n?>"
            ],
            [
                "id" => null,
                "script" => "<?php function dfm_strtolower(\$self) {\nreturn mb_strtolower(\$self, \"UTF8\");\n}\n?>"
            ],
            [
                "id" => null,
                "script" => "<?php function dfm_implode(\$sep,\$self) {\nreturn (is_array(\$self)) ? implode(\$sep, \$value) : \$self;\n}\n?>"
            ],
            [
                "id" => null,
                "script" => '<?php function dfm_html_entity_decode($self) { return html_entity_decode($self, ENT_QUOTES, "UTF-8"); } ?>'
            ],
            [
                "id" => null,
                "script" => "<?php function dfm_strip_tags(\$self) {\nreturn strip_tags(preg_replace(['!<br />!isU','!<br/>!isU','!<br>!isU'], [\" \",\" \",\" \"], \$self));\n} ?>"
            ],
            [
                "id" => null,
                "script" => "<?php function dfm_htmlentities(\$self) {\nreturn htmlspecialchars(\$self);\n}\n?>"
            ],
            [
                "id" => null,
                "script" => "<?php function dfm_substr(\$self,\$len,\$end) {\n\$value = substr(\$self, 0,\$len - 3);\n\$s = strrpos(\$value, \" \");\n\$value = substr(\$value, 0, \$s) . \$end;\nreturn \$value;\n} \n?>"
            ],
            [
                "id" => null,
                "script" => "<?php function inline(\$self) {\nreturn preg_replace('/(
|\n|\r|
|\t)/s', '', \n\$self);\n}\n?>"
            ]
        ];


        $data['variables'] = [
            [
                "id" => null,
                "name" => "configurable_sizes",
                "comment" => "Get all sizes available for a configurable product",
                "script" => "<?php
if (\$product->getTypeId() == 'configurable') {
	\$childProducts = \$product->getTypeInstance()->getUsedProducts(\$product);
	\$sizes = [];
	foreach (\$childProducts as \$child) {
    	\$sizes[] = \$child->getAttributeText('size');
  	}
    return implode(',', array_unique(\$sizes));
}
?>"
            ]
        ];


        $installer->getConnection()->truncateTable($installer->getTable("datafeedmanager_feeds"));
        foreach ($data['templates'] as $template) {
            $installer->getConnection()->insert($installer->getTable("datafeedmanager_feeds"), $template);
        }

        $installer->getConnection()->truncateTable($installer->getTable("datafeedmanager_functions"));
        foreach ($data['functions'] as $function) {
            $installer->getConnection()->insert($installer->getTable("datafeedmanager_functions"), $function);
        }

        $installer->getConnection()->truncateTable($installer->getTable("datafeedmanager_variables"));
        foreach ($data['variables'] as $variable) {
            $installer->getConnection()->insert($installer->getTable("datafeedmanager_variables"), $variable);
        }

        $installer->endSetup();
    }
}
