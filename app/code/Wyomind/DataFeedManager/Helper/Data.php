<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Helper;

/**
 * Data Feed Manager general helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_coreDate = null;
    protected $_dateFormats = [
        '{f}',
        'Y-m-d-{f}',
        '{f}-Y-m-d',
        'Y-m-d-H-i-s-{f}',
        '{f}-Y-m-d-H-i-s',
        'Y-m-d-H-i-s'
    ];
    protected $_fieldSeparators = [
        ';',
        ',',
        '|',
        '\t' => '\tab',
        '[|]'
    ];
    protected $_fieldProtectors = [
        '"',
        "'",
        '' => 'none'
    ];
    protected $_fieldEscapes = [
        '"',
        "\\"
    ];

    /**
     * Helper contructor
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
    ) {
        parent::__construct($context);
        $this->_coreDate = $coreDate;
    }

    public function getFinalFilename(
        $format,
        $filename,
        $updatedAt
    ) {
        return str_replace('{f}', $filename, date($format, strtotime($updatedAt)));
    }

    /**
     *
     * @param type $fn
     * @param type $ext
     * @return type
     */
    public function getDateFormats(
        $fn,
        $ext
    ) {
        $toReturn = [];
        foreach ($this->_dateFormats as $dateFormat) {
            $toReturn[] = [
                'value' => $dateFormat,
                'label' => str_replace('###', $fn, $this->_coreDate->date(str_replace('{f}', '###', $dateFormat)) . $ext)
            ];
        }
        return $toReturn;
    }

    public function getExtFromType($type)
    {
        $ext = ".ext";
        switch ($type) {
            case 1:
                $ext = ".xml";
                break;
            case 2:
                $ext = ".txt";
                break;
            case 3:
                $ext = ".csv";
                break;

            case 4:
                $ext = ".tsv";
                break;
            case 5:
                $ext = ".din";
                break;
            default:
                $ext = ".ext";
        }
        return $ext;
    }

    public function getFileFormats()
    {
        return [
            [
                'value' => 1,
                'label' => 'xml'
            ],
            [
                'value' => 2,
                'label' => 'txt'
            ],
            [
                'value' => 3,
                'label' => 'csv'
            ],
            [
                'value' => 4,
                'label' => 'tsv'
            ]
        ];
    }

    public function getEncodings()
    {
        return [
            [
                'value' => 'UTF-8',
                'label' => 'UTF-8'
            ],
            [
                'value' => 'Windows-1252',
                'label' => 'Windows-1252 (ANSI)'
            ],
        ];
    }

    public function getYesNoOptions()
    {
        return [
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => 1,
                'label' => __('Yes')
            ]
        ];
    }

    public function getFieldSeparators()
    {
        $toReturn = [];
        foreach ($this->_fieldSeparators as $key => $separator) {
            if (is_numeric($key)) {
                $toReturn[] = [
                    'value' => $separator,
                    'label' => $separator
                ];
            } else {
                $toReturn[] = [
                    'value' => $key,
                    'label' => $separator
                ];
            }
        }
        return $toReturn;
    }

    public function getFieldProtectors()
    {
        $toReturn = [];
        foreach ($this->_fieldProtectors as $key => $protector) {
            if (is_numeric($key)) {
                $toReturn[] = [
                    'value' => $protector,
                    'label' => $protector
                ];
            } else {
                $toReturn[] = [
                    'value' => $key,
                    'label' => $protector
                ];
            }
        }
        return $toReturn;
    }

    public function getFieldEscapes()
    {
        $toReturn = [];
        foreach ($this->_fieldEscapes as $key => $escape) {
            if (is_numeric($key)) {
                $toReturn[] = [
                    'value' => $escape,
                    'label' => $escape
                ];
            } else {
                $toReturn[] = [
                    'value' => $key,
                    'label' => $escape
                ];
            }
        }
        return $toReturn;
    }

    public function stripTagsContent(
        $text,
        $tags = '',
        $invert = false
    ) {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if (is_array($tags) and count($tags) > 0) {
            if ($invert == false) {
                return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            } else {
                return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
            }
        } elseif ($invert == false) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return strip_tags($text);
    }
    
    
    public function strReplaceFirst(
        $search,
        $replace,
        $subject
    ) {
    
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }
}
