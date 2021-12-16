<?php

namespace lib\rest\utils;

class NetVersionHelper
{
    public function translateNetVersion($tf)
    {
        $tf = strtolower($tf);
        switch($tf){
            case("native"): return "native";
            case(".netframework4.6.1"): return "net461";
            case(".netframework4.6"): return "net46";
            case(".netframework4.5.2"): return "net452";
            case(".netframework4.5.1"): return "net451";
            case(".netframework4.5"): return "net45";
            case(".netframework3.5"): return "net35";
            case(".netframework4.0"): return "net40";
            case(".netframework3.0"): return "net30";
            case(".netframework2.0"): return "net20";
            case(".netframework1.0"): return "net10";
            default: return $tf;
        }
    }
}