<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Generate data feed action
 */
class Generate extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Execute action
     */
    public function execute()
    {
        $request = $this->getRequest();
        
        $id = $request->getParam('id');
        
        $model = $this->_objectManager->create('Wyomind\DataFeedManager\Model\Feeds');
        $model->limit = 0;
            
        $model->load($id);
        try {
            $timeStart = time();
            $model->generateFile($request);
            $timeEnd = time();

            $time = $timeEnd - $timeStart;
            if ($time < 60) {
                $time = ceil($time) . ' sec. ';
            } else {
                $time = floor($time / 60) . ' min. ' . ($time % 60) . ' sec.';
            }
            
            $filename = preg_replace('/^\//', '', $model->getPath() . ($model->getPath() == "/" ? "" : "/") . $this->dfmHelper->getFinalFilename($model->getDateformat(), $model->getName(), $model->getUpdatedAt())) . $this->dfmHelper->getExtFromType($model->getType());
            $url = $model->getStoreBaseUrl().$filename;
            $url = preg_replace('/([^\:])\/\//', '$1/', $url);
            $url = str_replace('/pub/', '/', $url);
            $report = "<table>
                <tr><td align='right' width='150'>Processing time &#8614; </td><td>$time</td></tr>
                <tr><td align='right'>Product inserted &#8614; </td><td>$model->inc</td></tr>
                <tr><td align='right'>Generated file &#8614; </td><td><a href='$url' target='_blank'>$url</a></td></tr>
            </table>";

            
             
            $this->messageManager->addSuccess(
                __("The data feed ")
                . "\""
                .  $filename
                . "\"" . __(" has been generated.")
                . "<br/><br/>"
                . $report
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to generate the data feed.').'<br/><br/>'.$e->getMessage());
        }
       
        
        if ($request->getParam('generate_i')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->setParams(['id'=>$id]);
            return $resultForward->forward("edit");
        } else {
            return $this->resultForwardFactory->create()->forward("index");
        }
        
        
    }
}
