<?php

class PDFGenerator
{
    var $pdf;
    var $pageCount;
    var $input;
    var $protection;
    var $meta;

    const NONE = 'none';
    const ALL = 'all';
    const EVEN = 'even';
    const ODD = 'odd';

    function __construct($input, $protection, $meta)
    {
        $this->input = $input;
        $this->protection = $protection;
        $this->meta = $meta;
    }

    public function generate($output, $exclude = null, $reverse = false, $cutlines = null)
    {
        $this->trace('Generate PDF...');
        $this->trace('  -- '.$output);
        $this->trace('  -- exclude '.$exclude.($reverse ? ' + reverse' : '').($cutlines ? ' + cutline on '.$cutlines : ''));

        $this->pdf = new FPDI();
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pageCount = $this->pdf->setSourceFile($this->input);

        $pageNo = 1;
        $n = $this->pageCount;
        $pages = array();

        // Store wanted pages
        for ($pageNo; $pageNo <= $n; $pageNo++)
        {
            $even = $this->isEven($pageNo);

            if($exclude == self::EVEN && $even)
                continue;
            else if($exclude == self::ODD && !$even)
                continue;

            $pages[] = array('id' => $this->pdf->importPage($pageNo), 'even' => $even);
        }

        // Add pages in PDF
        $n = count($pages);

        if($reverse)
            $pages = array_reverse($pages);

        $this->trace('  -- '.$n.' pages');

        for ($i = 0; $i < $n; $i++)
        {
            $templateId = $pages[$i]['id'];
            $even = $pages[$i]['even'];

            // get the size of the imported page
            $size = $this->pdf->getTemplateSize($templateId);

            // create a page (landscape or portrait depending on the imported page size)
            if ($size['w'] > $size['h']) {
                $this->pdf->AddPage('L', array($size['w'], $size['h']));
            } else {
                $this->pdf->AddPage('P', array($size['w'], $size['h']));
            }

            // use the imported page
            $this->pdf->useTemplate($templateId);

            $x = floor($size['w']/2);
            $h = $size['h'];

            // draw cut lines
            if($cutlines)
            {
                if($cutlines == self::ALL || ($cutlines == self::EVEN && $even) || $cutlines == self::ODD && !$even)
                {
                    $style = array('width' => 0.1, 'color' => array(224, 224, 224));
                    $this->pdf->Line($x, 10, $x, 15, $style);
                    $this->pdf->Line($x, $h-15, $x, $h-10, $style);
                }
            }
        }

        if($this->protection)
            $this->pdf->SetProtection($this->protection, '', null, 0, null);


        // set document information
        /*
        $this->pdf->AddPage();
        $txt = 'version';
        $this->pdf->Write(0, $txt, '', 0, 'L', true, 0, false, false, 0);
        */

        // Add metas
        if($this->meta)
        {
            if(isset($this->meta['creator']))
                $this->pdf->SetCreator($this->meta['creator']);

            if(isset($this->meta['author']))
                $this->pdf->SetAuthor($this->meta['author']);

            if(isset($this->meta['title']))
                $this->pdf->SetTitle($this->meta['title']);

            if(isset($this->meta['subject']))
                $this->pdf->SetSubject($this->meta['subject']);

            if(isset($this->meta['keywords']))
                $this->pdf->SetSubject($this->meta['keywords']);
        }

        // Output the new PDF
        $this->pdf->Output($output, 'F');
    }

    function trace($message)
    {
        echo $message."\n";
    }

    function isEven($value)
    {
        return $value % 2 == 0;
    }
}


