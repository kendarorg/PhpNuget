<?php

namespace lib\db\file;

use lib\db\Executor;
use lib\db\parser\InternalTypeBuilder;
use lib\db\parser\Operator;

class FileDbExecutor extends Executor
{


    public function doGroupBy($subject)
    {
        if (sizeof($this->_groupClause) == 0) return $subject;
        $result = array();
        $keys = array();
        $counters = array();
        $countersIndex = array();

        foreach ($subject as $item) {
            $k = "";
            for ($i = 0; $i < sizeof($this->_groupClause); $i++) {
                $fld = $this->_groupClause[$i];
                $k .= "#" . $item->$fld;
            }
            if (!array_key_exists($k, $keys)) {
                $keys[$k] = true;
                $result[] = $item;
                $counters[] = 1;
                $countersIndex[$k] = sizeof($counters) - 1;
            } else {
                $counters[$countersIndex[$k]]++;
            }
        }

        for ($i = 0; $i < sizeof($result); $i++) {
            $result[$i]->count = $counters[$i];
        }

        return $result;
    }

    /*
	The comparison function must return an integer
	less than, if the first argument is considered to be less then the second
	equal to, if the first argument is considered to be equal to the second
	greater than zero if the first argument is considered to be greater than the second.
	*/
    public function _doSort($f, $s)
    {
        $print = false;


        for ($i = 0; $i < sizeof($this->_sortClause); $i++) {
            $so = $this->_sortClause[$i];
            $row = $so->Field;//$this->fieldsMatch[];
            $asc = $so->Asc;
            $type = $this->_types[strtolower($row)];
            $realRow = $this->fieldsMatch[strtolower($row)];

            $res = $this->_cmp($f->$realRow, $s->$realRow, $asc, $type);
            if ($res > 0) {

                //if($print)echo $f->Title." ".$f->Version.">".$s->Title." ".$s->Version."\r\n";
                return $asc ? 1 : -1;
            } else if ($res < 0) {
                //throw new \Exception("AA");
                //if($print)echo $f->Title." ".$f->Version."<".$s->Title." ".$s->Version."\r\n";
                return $asc ? -1 : 1;
            }
        }

        //if($print)echo $f->Title." ".$f->Version."==".$s->Title." ".$f->Version."\r\n";
        return 0;
    }

    /*
    The comparison function must return an integer
    less than, if the first argument is considered to be less then the second
    equal to, if the first argument is considered to be equal to the second
    greater than zero if the first argument is considered to be greater than the second.
    */
    public function _cmp($f, $s, $asc, $type)
    {

        if (($fId = $this->isExternalType($f)) >= 0 && ($sId = $this->isExternalType($s)) >= 0) {
            $ft = $this->externalTypes[$fId]->buildToken($f);
            $st = $this->externalTypes[$sId]->buildToken($s);
            $arg = array();
            $arg[] = $ft;
            $arg[] = $st;
            if ($this->externalTypes[$sId]->dolt($arg)->Value) return -1;
            if ($this->externalTypes[$sId]->dogt($arg)->Value) return 1;
            return 0;
        }
        switch ($type) {
            case("boolean"):
            case("number"):
                return $f > $s;
            case("string"):
            case("date"):
                return strcasecmp($f, $s);
        }

        return 0;
    }


    function substringof($args)
    {
        $res = InternalTypeBuilder::buildBool(contains(strtolower($args[0]->Value), strtolower($args[1]->Value)));
        /*echo ($args[0]->Value)."\n";
        echo ($args[1]->Value)."\n";
        var_dump($res);
        echo "===============\n";*/
        return $res;
    }

    function doand($args)
    {
        for ($i = 0; $i < sizeof($args); $i++) {
            if (!$args[$i]->Value) {
                return InternalTypeBuilder::buildBool(false);
            }
        }
        return InternalTypeBuilder::buildBool(true);
    }

    function door($args)
    {
        for ($i = 0; $i < sizeof($args); $i++) {
            if ($args[$i]->Value) {
                return InternalTypeBuilder::buildBool(true);
            }
        }
        return InternalTypeBuilder::buildBool(false);
    }

    function tolower($args)
    {
        return InternalTypeBuilder::buildItem(strtolower($args[0]->Value), $args[0]->Type, $args[0]->Id);
    }

    function toupper($args)
    {
        return InternalTypeBuilder::buildItem(strtoupper($args[0]->Value), $args[0]->Type, $args[0]->Id);
    }

    function startsWithInt($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    function endsWithInt($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    function startswith($args)
    {
        return InternalTypeBuilder::buildBool($this->startsWithInt($args[0]->Value, $args[1]->Value));
    }

    function endswith($args)
    {
        return InternalTypeBuilder::buildBool($this->endsWithInt($args[0]->Value, $args[1]->Value));
    }

    function dosubstringof($args)
    {
        $l = $args[0];
        $r = $args[1];
        $pos = stripos($r->Value, $l->Value);
        if ($pos === false) {
            return InternalTypeBuilder::buildBool(false);
        } else {
            return InternalTypeBuilder::buildBool(true);
        }
    }


    public function doSort(&$subject)
    {

        if (sizeof($this->_sortClause) == 0) return $subject;

        //throw new \Exception(json_encode($subject));
        usort($subject, array($this, "_doSort"));


        return $subject;
    }

    function doeq($args)
    {
        $l = $args[0];
        $r = $args[1];

        if ($l->Type == "string" || $r->Type == "string") {
            return InternalTypeBuilder::buildBool(strtolower($l->Value) == strtolower($r->Value));
        }

        return InternalTypeBuilder::buildBool($l->Value == $r->Value);
    }

    function doneq($args)
    {
        $l = $args[0];
        $r = $args[1];
        return InternalTypeBuilder::buildBool($l->Value != $r->Value);
    }

    function dogt($args)
    {
        $l = $args[0];
        $r = $args[1];
        return InternalTypeBuilder::buildBool($l->Value > $r->Value);
    }

    function dogte($args)
    {
        $l = $args[0];
        $r = $args[1];
        if ($l->Value == $r->Value) return InternalTypeBuilder::buildBool(true);
        return InternalTypeBuilder::buildBool($l->Value > $r->Value);
    }

    function dolt($args)
    {
        $l = $args[0];
        $r = $args[1];
        return InternalTypeBuilder::buildBool($l->Value < $r->Value);
    }

    function dolte($args)
    {
        $l = $args[0];
        $r = $args[1];
        if ($l->Value == $r->Value) return InternalTypeBuilder::buildBool(true);
        return InternalTypeBuilder::buildBool($l->Value < $r->Value);
    }

    protected function executeFunctionInt($name, $params)
    {
        return $this->$name($params);
    }
}