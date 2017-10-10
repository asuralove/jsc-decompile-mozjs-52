<?php
/**
 * Created by PhpStorm.
 * User: irelance
 * Date: 2017/10/10
 * Time: 上午11:30
 */

namespace Irelance\Mozjs52\Xdr;

use Irelance\Mozjs52\Constant;


trait Common
{
    protected function todec($length = 4)//length include start
    {
        $result = '';
        for ($i = $this->parseIndex + $length - 1; $i >= $this->parseIndex; $i--) {
            $result .= sprintf('%02s', dechex($this->bytecodes[$i]));
        }
        $this->parseIndex += $length;
        return hexdec($result);
    }

    public function xdrConst()
    {
        $const = [
            'type' => $this->todec(),
        ];
        switch ($const['type']) {
            case 0:
                $const['value'] = $this->todec();
                break;
            case 1:
                $const['value'] = $this->todec(8);
                break;
            case 2:
                $const['value'] = $this->XDRAtom();
                break;
            case 3:
                $const['value'] = true;
                break;
            case 4:
                $const['value'] = false;
                break;
            case 5:
                $const['value'] = null;
                break;
            case 6:
                $object = $this->xdrCK_JSObject();
                $const['value'] = "__OBJECT__";
                $const['extra'] = $object;
                break;
            case 7:
                $const['value'] = "__VOID__";
                break;
            case 8:
                $const['value'] = "__HOLE__";
                break;
            default:
                $const['value'] = "__ERROR__";
                break;
        }
        return $const;
    }

    public function XDRInterpretedFunction()
    {
        $firstword = $this->todec();
        if ($firstword & Constant::_FirstWordFlag['HasAtom']) {
            $this->XDRAtom();
        }
        $flagsword = $this->todec();
        if ($firstword & Constant::_FirstWordFlag['IsLazy']) {
            $this->XDRLazyScript();
        } else {
            $this->XDRScript();
        }
    }

    public function XDRLazyScript()
    {
        //XDRLazyScript
        $begin = $this->todec();
        $end = $this->todec();
        $lineno = $this->todec();
        $column = $this->todec();
        $packedFields = $this->todec(8);
        //todo XDRLazyClosedOverBindings for 0 -> lazy->numClosedOverBindings()
        $endOfScopeSentinel = $this->todec(1);
        //todo for 0 -> lazy->numInnerFunctions() XDRInterpretedFunction
        $this->XDRInterpretedFunction();
    }
}
