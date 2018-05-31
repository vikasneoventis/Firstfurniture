<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$repository = $obj->create('Magento\Catalog\Model\ProductRepository');
$product = $repository->getById($_POST['productId']);

$data = $product->getTypeInstance()->getConfigurableOptions($product);

$options = array();

function array_equal($a1, $a2) {
  return !array_diff($a1, $a2) && !array_diff($a2, $a1);
}

foreach($data as $attr)
{
  foreach($attr as $p)
  {
    $options[$p['sku']][$p['attribute_code']] = $p['option_title'];
    $attributes[] = $p['attribute_code'];
  }
}

$attributes = array_values(array_unique($attributes));
$noOfAttributes = count($attributes);

$optionsSelected = explode(",",$_POST['option_selected']);


$s=0;
foreach($options as $sku =>$d)
{
  $pr = $repository->get($sku);
  $productData = $pr->getData();
  foreach($attributes as $attribute)
  {
      $arr1[$s]['attribute'][] = $productData[$attribute];
  }
  $arr1[$s]['sku'] = $sku;
  $s++;
}
if(isset($arr1) && count($arr1[0]['attribute']) == count($optionsSelected))
{
    foreach($arr1 as $arr)
    {
        
       if(is_array($arr))
       {
          foreach($arr as $key=>$value)
          {
              if(is_array($value) && $key == 'attribute')
              {
                  if(array_equal($value, $optionsSelected))
                  {
                      $selectedSku = $arr['sku'];
                  }
              }
          }
       }
    }
}
if(isset($selectedSku))
{
  $pr = $repository->get($selectedSku);
  echo "£".number_format($pr->getPrice(), 2);die;
}
