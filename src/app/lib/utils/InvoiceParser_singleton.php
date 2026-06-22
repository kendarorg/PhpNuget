<?php

class InvoiceParser {
    var $log;
    var $globalErrors;
    var $translator;
    var $converter;

    var $contacts;
    public function __construct()
    {
        $this->log  = LogManager::getLogger("InvoiceParser");
        $this->globalErrors = GlobalRegistry::get("GlobalErrors");
        $this->translator = GlobalRegistry::get("Translator");
        $this->converter = GlobalRegistry::get("Converter");
        $this->contacts = GlobalRegistry::get("ContactsModel");
    }

    function parse(&$xmlString, $isP7M)  //parseeinvoice
    {

        $result =[];
        try {
            $this->log->debug("Parsing e-invoice");
            $full = $this->parseInvoiceXml($xmlString,$isP7M);

            for($q=0;$q<sizeof($full['data']);$q++) {

                $this->log->trace("Loading invoices data ".$q);
                $single= $full['data'][$q];
                // Calculate totals
                $totImponibile = 0;
                $totIva = 0;

                // Get or create a subject from the first line description
                $detail = $single['Fattura']['Causale']."\n\n";

                // Process line items to calculate totals
                foreach ( $single['Linee'] as $linea) {
                    $detail.= "Linea:\n".$linea['Descrizione']."\n";
                }



                // Format the invoice data
                $invoiceData = [
                    'committente' => $single['Header']['Cliente']['Denominazione'],
                    'prestatore' => $single['Header']['Fornitore']['Denominazione'],
                    'number' => $single['Fattura']['Numero'],
                    'date' => $single['Fattura']['Data'],
                    'subject' => trim($detail),
                    'totImponibile' =>$this->converter->toFloat($single['Fattura']['ImponibileImporto']),
                    'totIva' => $this->converter->toFloat($single['Fattura']['ImportoTotaleDocumento'])-$this->converter->toFloat($single['Fattura']['ImponibileImporto']),
                    'data' => json_encode($single),
                    'paymentMode'=>'',
                    'bankSupport'=>'',
                    'committenteId'=>null,
                    'prestatoreId'=>null,
                    'notes'=>'',
                    'xml'=>"<FatturaElettronica>".$full['header'].$full['source'][$q]."</FatturaElettronica>"// Store the original XML for reference
                ];
                $this->identifyInvoiceExtras($single,$invoiceData);
                $result[]= $invoiceData;
            }

            return $result;
        } catch (Exception $e) {
            $this->globalErrors->addGlobalError( $this->translator->translate('ERROR_MESSAGE','CREATE','INVOICE'),$e);
            return false;
        }
    }


    function extractFromP7M($xmlContent,$base64=false) {

        // Method 3: Direct binary search for XML content
        preg_match('/<[a-zA-Z0-9]*:?FatturaElettronica/', $xmlContent, $matchesStart, PREG_OFFSET_CAPTURE);
        preg_match('/<\\/[a-zA-Z0-9]*:?FatturaElettronica>/', $xmlContent, $matchesEnd, PREG_OFFSET_CAPTURE);

        if (sizeof($matchesStart)>0 && sizeof($matchesEnd)>0) {
            $xmlStart = $matchesStart[0][1];
            $xmlEnd = $matchesEnd[0][1]+strlen($matchesEnd[0][0]);
            $xmlContent = substr($xmlContent, $xmlStart, $xmlEnd-$xmlStart);
        }else if (strpos($xmlContent, ':FatturaElettronica') !== false) {
            $xmlStart = strpos($xmlContent, ':FatturaElettronica');
            $xmlContent = substr($xmlContent, $xmlStart);
            $xmlEnd = strpos($xmlContent, '</ns3:FatturaElettronica>');
            $xmlContent = substr($xmlContent, 0, $xmlEnd + strlen('</ns3:FatturaElettronica>'));

        } else if (strpos($xmlContent, '<FatturaElettronica') !== false) {
            $xmlStart = strpos($xmlContent, '<FatturaElettronica');
            $xmlContent = substr($xmlContent, $xmlStart);
            $xmlEnd = strpos($xmlContent, '</FatturaElettronica>');
            $xmlContent = substr($xmlContent, 0, $xmlEnd + strlen('</FatturaElettronica>'));

        }else if(!$base64){

            $xmlContent = preg_replace('/\s+/', '', $xmlContent);
            $xmlContent = base64_decode($xmlContent);
            $xmlContent = preg_replace('/[^\x20-\x7E]/', '', $xmlContent);
            return $this->extractFromP7M($xmlContent,true);
        }
        $xmlContent = preg_replace('/[^\x20-\x7E]/', '', $xmlContent);
        return $xmlContent;
        // Create temporary files
//    $tempP7mFile = tempnam(sys_get_temp_dir(), 'p7m_');
//    $tempOutputFile = tempnam(sys_get_temp_dir(), 'extracted_');
//
//    try {
//        file_put_contents($tempP7mFile, $p7mContent);
//
//        // Try to read as PKCS7
//        openssl_pkcs7_verify($tempP7mFile, PKCS7_NOVERIFY, $tempOutputFile);
//        return file_get_contents($tempOutputFile);
//
//
//        if (file_exists($tempP7mFile)) {
//            unlink($tempP7mFile);
//        }
//        if (file_exists($tempOutputFile)) {
//            unlink($tempOutputFile);
//        }
//    }
    }

    function getIf($callback,$defaultValue = ''){
        try {
            $data = $callback();
            return $data;
        } catch (Exception $e) {
            return $defaultValue;
        }
    }

    function parseInvoiceXml($xmlContent,$p7m)
    {
        if($p7m){
            $xmlContent=$this->extractFromP7M($xmlContent);
        }
        $xml = simplexml_load_string($xmlContent);
        $header = $xml->children()->FatturaElettronicaHeader;
        $result=[];
        $xmlContents = [];
        $xmlHeader = $header->asXML();
        foreach ($xml->children()->FatturaElettronicaBody as $body) {
            $json = [];
            $xmlContents[]=$body->asXML();

            // === Header ===
            $trasmissione = $header->DatiTrasmissione;
            $cedente = $header->CedentePrestatore;
            $cessionario = $header->CessionarioCommittente;
            $json["Version"] = "2";
            $json['Header'] = [
                'Trasmissione' => [
                    'IdTrasmittente' => $this->getIf(function () use ($trasmissione){return (string)$trasmissione->IdTrasmittente->IdPaese."-".$trasmissione->IdTrasmittente->IdCodice;}),
                    'ProgressivoInvio' => $this->getIf(function () use ($trasmissione){return (string)$trasmissione->ProgressivoInvio;}),
                    'FormatoTrasmissione' => $this->getIf(function () use ($trasmissione){return (string)$trasmissione->FormatoTrasmissione;}),
                    'CodiceDestinatario' => $this->getIf(function () use ($trasmissione){return (string)$trasmissione->CodiceDestinatario;}),
                    'PECDestinatario' => $this->getIf(function () use ($trasmissione){return (string)$trasmissione->PECDestinatario;})
                ],
                'Fornitore' => [
                    'Denominazione' => $this->getIf(function () use ($cedente){return (string)$cedente->DatiAnagrafici->Anagrafica->Denominazione;}),
                    'PartitaIVA' => $this->getIf(function () use ($cedente){return (string)$cedente->DatiAnagrafici->IdFiscaleIVA->IdPaese."-".$cedente->DatiAnagrafici->IdFiscaleIVA->IdCodice;}),
                    'CodiceFiscale' => $this->getIf(function () use ($cedente){return (string)$cedente->DatiAnagrafici->CodceFiscale;}),
                    'RegimeFiscale' => $this->getIf(function () use ($cedente){return (string)$cedente->DatiAnagrafici->RegimeFiscale;}),
                    'Indirizzo' => $this->getIf(function () use ($cedente){return (string)$cedente->Sede->Indirizzo;}),
                    'CAP' => $this->getIf(function () use ($cedente){return (string)$cedente->Sede->CAP;}),
                    'Comune' => $this->getIf(function () use ($cedente){return (string)$cedente->Sede->Comune;}),
                    'Provincia' => $this->getIf(function () use ($cedente){return (string)$cedente->Sede->Provincia;}),
                    'NazioneSede' => $this->getIf(function () use ($cedente){return (string)$cedente->Sede->Nazione;})
                ],
                'Cliente' => [
                    'Denominazione' => $this->getIf(function () use ($cessionario){return (string)$cessionario->DatiAnagrafici->Anagrafica->Denominazione;}),
                    'PartitaIVA' => $this->getIf(function () use ($cessionario){return (string)$cessionario->DatiAnagrafici->IdFiscaleIVA->IdPaese."-".$cessionario->DatiAnagrafici->IdFiscaleIVA->IdCodice;}),
                    'CodiceFiscale' => $this->getIf(function () use ($cessionario){return (string)$cessionario->DatiAnagrafici->CodceFiscale;}),
                    'RegimeFiscale' => $this->getIf(function () use ($cessionario){return (string)$cessionario->DatiAnagrafici->RegimeFiscale;}),
                    'Indirizzo' => $this->getIf(function () use ($cessionario){return (string)$cessionario->Sede->Indirizzo;}),
                    'CAP' => $this->getIf(function () use ($cessionario){return (string)$cessionario->Sede->CAP;}),
                    'Comune' => $this->getIf(function () use ($cessionario){return (string)$cessionario->Sede->Comune;}),
                    'Provincia' => $this->getIf(function () use ($cessionario){return (string)$cessionario->Sede->Provincia;}),
                    'NazioneSede' => $this->getIf(function () use ($cessionario){return (string)$cessionario->Sede->Nazione;})
                ]
            ];

            // === Invoice Data ===
            $datiGenerali = $body->DatiGenerali->DatiGeneraliDocumento;
            $datiBeniServizi = $body->DatiBeniServizi;

            $json['Fattura'] = [
                'Causale' => $this->getIf(function () use ($datiGenerali){
                    $result="";
                    foreach($datiGenerali->Causale as $causale){
                        $result.= ((string)$causale)."\n";
                    }
                    return trim($result);
                }),
                'ImponibileImporto'=>0.0,
                'Numero' => $this->getIf(function () use ($datiGenerali){return (int)$datiGenerali->Numero;}),
                'Data' => $this->getIf(function () use ($datiGenerali){return (string)$datiGenerali->Data;}),
                'ImportoTotaleDocumento' =>$this->converter->toFloat($this->getIf(function () use ($datiGenerali){return  (float)$datiGenerali->ImportoTotaleDocumento;})),
                'Divisa' => $this->getIf(function () use ($datiGenerali){return (string)$datiGenerali->Divisa;}),
                'TipoDocumento' => $this->getIf(function () use ($datiGenerali){return (string)$datiGenerali->TipoDocumento;}),
            ];

            $json['DatiPagamento']=[
                "Pagamenti"=>[]
            ];
            $counter = 0;
            foreach($body->DatiPagamento as $datiPagamento) {

                $json['DatiPagamento']['Pagamenti'][]= [
                    'Id'=>$counter,
                    'CondizioniPagamento'             => $this->getIf(function () use ($datiPagamento) {
                        return (string)$datiPagamento->CondizioniPagamento;
                    }),
                    'ModalitaPagamento'               => $this->getIf(function () use ($datiPagamento) {
                        return (string)$datiPagamento->DettaglioPagamento->ModalitaPagamento;
                    }),
                    'DataRiferimentoTerminiPagamento' => $this->getIf(function () use ($datiPagamento) {
                        return (string)$datiPagamento->DettaglioPagamento->DataRiferimentoTerminiPagamento;
                    }),
                    'GiorniTerminiPagamento'          => $this->getIf(function () use ($datiPagamento) {
                        return (string)$datiPagamento->DettaglioPagamento->GiorniTerminiPagamento;
                    }),
                    'DataScadenzaPagamento'           => $this->getIf(function () use ($datiPagamento) {
                        return (string)$datiPagamento->DettaglioPagamento->DataScadenzaPagamento;
                    }),
                    'ImportoPagamento'                => $this->converter->toFloat($this->getIf(function () use ($datiPagamento) {
                        return (float)$datiPagamento->DettaglioPagamento->ImportoPagamento;
                    })),
                    'IBAN'                            => $this->getIf(function () use ($datiPagamento) {
                        return (string)$datiPagamento->DettaglioPagamento->IBAN;
                    }),
                ];
                $counter++;
            }


            $drAliquote=[];

            foreach ($datiBeniServizi->DatiRiepilogo as $linea) {
                $dr=[
                    'AliquotaIVA' => $this->converter->toFloat($this->getIf(function () use ($linea){return (float)$linea->AliquotaIVA;})),
                    'ImponibileImporto' => $this->converter->toFloat($this->getIf(function () use ($linea){return (float)$linea->ImponibileImporto;})),
                    'Imposta' => $this->converter->toFloat($this->getIf(function () use ($linea){return (float)$linea->Imposta;})),
                    'EsigibilitaIVA' => $this->getIf(function () use ($linea){return (string)$linea->EsigibilitaIVA;}),
                    'RiferimentoNormativo'=> $this->getIf(function () use ($linea){return (string)$linea->RiferimentoNormativo;}),
                ];
                $drAliquote[$dr['AliquotaIVA'].''] = $dr;

            }

            $json['Linee'] = [];

            $json['Fattura']['ImponibileImporto']=0.0;
            $json['Fattura']['ImportoTotaleDocumento']=0.0;
            $json['DatiPagamento']['ImportoPagamento']=0.0;
            foreach ($datiBeniServizi->DettaglioLinee as $linea) {
                $li = [
                    'Descrizione' => $this->getIf(function () use ($linea){
                        $result="";
                        foreach($linea->Descrizione as $causale){
                            $result.= $causale."\n";
                        }
                        return trim($result);
                    }),
                    'NumeroLinea' => $this->converter->toInt($this->getIf(function () use ($linea){return (int)$linea->NumeroLinea;})),
                    'Quantita' => $this->converter->toFloat($this->getIf(function () use ($linea){return (float)$linea->Quantita;},1.0)),
                    'PrezzoUnitario' => $this->converter->toFloat($this->getIf(function () use ($linea){return (float)$linea->PrezzoUnitario;})),
                    'PrezzoTotale' => $this->converter->toFloat($this->getIf(function () use ($linea){return (float)$linea->PrezzoTotale;})),
                    'AliquotaIVA' => $this->converter->toFloat($this->getIf(function () use ($linea){return (string)$linea->AliquotaIVA;}))
                ];
                if($li['Quantita']<0.0101){
                    $li['Quantita']=1.0;
                }

                $json['Fattura']['ImponibileImporto'] += $li['PrezzoTotale'];
                if($li['AliquotaIVA']>0.01) {
                    $json['Fattura']['ImportoTotaleDocumento'] +=
                        (($li['PrezzoTotale']/100)*$li['AliquotaIVA']) + $li['PrezzoTotale'];
                }else{
                    $json['Fattura']['ImportoTotaleDocumento'] +=$li['PrezzoTotale'];
                }
                $datiGenerali=$drAliquote[$li['AliquotaIVA'].''];
                $li['EsigibilitaIVA']=$datiGenerali['EsigibilitaIVA'];
                $li['RiferimentoNormativo']=$datiGenerali['RiferimentoNormativo'];
                $json['Linee'][]=$li;
            }
            $json['DatiPagamento']['ImportoPagamento']=$json['Fattura']['ImportoTotaleDocumento'];
            $result[]=$json;
        }

        $data=[
            'data' => $result,
            'source'=>$xmlContents,
            'header'=>$xmlHeader
        ];
        return $data;
    }

    public function identifyInvoiceExtras(&$contentExpanded,&$invoiceData){
        $companyVat =GlobalRegistry::get("COMPANY_VAT");
        $pIvaCliente = getOrNull($contentExpanded,"Header","Cliente","PartitaIVA");
        $cfCliente = getOrNull($contentExpanded,"Header","Cliente","CodiceFiscale");
        $pIvaFornitore = getOrNull($contentExpanded,"Header","Fornitore","PartitaIVA");
        $cfFornitore = getOrNull($contentExpanded,"Header","Fornitore","CodiceFiscale");
        if($pIvaCliente == $companyVat || $cfCliente == $companyVat){
            $invoiceData["kind"]= "Fornitore";
        }else if($pIvaFornitore == $companyVat || $cfFornitore == $companyVat){
            $invoiceData["kind"]= "Cliente";
        }else{
            $invoiceData["kind"]= "Fornitore";
        }
        $invoiceData["committente"] = getOrNull($contentExpanded,"Header","Cliente","Denominazione");
        $invoiceData["prestatore"] = getOrNull($contentExpanded,"Header","Fornitore","Denominazione");
        $tmp = $this->getByTaxCode($pIvaCliente);
        if($tmp==null)$tmp = $this->getByTaxCode($cfCliente);
        if($tmp!=null){
            $invoiceData["committenteId"]=$tmp["id"];
            if(!empty($tmp["name"])) {
                $invoiceData["committente"] = $tmp["name"];
            }
        }
        $tmp = $this->getByTaxCode($pIvaFornitore);
        if($tmp==null)$tmp = $this->getByTaxCode($cfFornitore);
        if($tmp!=null){
            $invoiceData["prestatoreId"]=$tmp["id"];
            if(!empty($tmp["name"])) {
                $invoiceData["prestatore"] = $tmp["name"];
            }
        }
    }

    public function adaptTaxCode($taxCode){
        if($taxCode=="-") return null;
        if ($taxCode == null || strlen(trim($taxCode)) == 0) return null;
        $expl = explode("-", $taxCode);
        if (sizeof($expl) == 2) {
            $taxCode = $expl[1];
        }
        return $taxCode;
    }

    public function getByTaxCode($taxCode){
        $taxCode=$this->adaptTaxCode($taxCode);
        if ($taxCode == null) return null;
        $sq = new SearchQuery();
        $sq->fields=["id","name","ragioneSociale"];
        $sq->searchTerms=[
            "taxCode"=>$taxCode
        ];
        $sq->count=1;
        $res =$this->contacts->search($sq);
        if(count($res)==0){return null;}
        $name = $res[0]["name"];
        if(empty($name)){
            $name = $res[0]["ragioneSociale"];
        }
        return [
            "id"=>$res[0]['id'],
            "name"=>$name
            ];
    }

}
