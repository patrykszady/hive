@if(isset($item->valueObject))
    @php
        $quantity = isset($item->valueObject->Quantity->valueNumber) ? $item->valueObject->Quantity->valueNumber : NULL;

        if(isset($item->valueObject->Price->valueNumber)){
            $price_each = $item->valueObject->Price->valueNumber;
        }elseif(isset($item->valueObject->TotalPrice->valueNumber)){
            $price_each = $item->valueObject->TotalPrice->valueNumber;
        }elseif(isset($item->valueObject->UnitPrice->valueCurrency)){
            $price_each = $item->valueObject->UnitPrice->valueCurrency->amount;
        }else{
            $price_each = NULL;
        }

        if(isset($item->valueObject->TotalPrice)){
            $price_total = $item->valueObject->TotalPrice->valueNumber;
        }elseif(isset($item->valueObject->Amount)){
            $price_total = $item->valueObject->Amount->valueCurrency->amount;
        }else{
            $price_total = NULL;
        }

        if(is_null($quantity)){
            if($price_each == $price_total){
                $quantity = 1;
            }else{
                if(!is_null($price_total) && !is_null($price_each)){
                    $quantity = $price_total / $price_each;
                }else{
                    $quantity = 1;
                }
            }
        }

        if(!is_null($price_total)){
            $price_total = money($price_total);
        }

        if(!is_null($price_each)){
            $price_each = money($price_each);
        }

        if(isset($item->valueObject->ProductCode->valueString)){
            $product_code = '# ' . $item->valueObject->ProductCode->valueString;
        }else{
            $product_code = NULL;
        }

        if(isset($item->valueObject->Description)){
            $desc = $item->valueObject->Description->valueString;
        }else{
            $desc = $item->content;
        }

        $line_details = [
        1 => [
            //$item->valueObject->Quantity->valueNumber . ' @ ' . $item->valueObject->Price->valueNumber . ' = ' . money($item->valueObject->TotalPrice->valueNumber)
            'text' => $quantity . ' @ ' . $price_each . ' = ' . $price_total,
                // isset($item->valueObject->Quantity) ? $item->valueObject->Quantity->valueNumber : ''
                // . ' @ ' .
                // isset($item->valueObject->Price) ? $item->valueObject->Price->valueNumber : ''
                // . ' = ' .
                // isset($item->valueObject->TotalPrice) ? money($item->valueObject->TotalPrice->valueNumber) : '',
            'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z'
            ],
        2 => [
            //$item->valueObject->Quantity->valueNumber . ' @ ' . $item->valueObject->Price->valueNumber . ' = ' . money($item->valueObject->TotalPrice->valueNumber)
            'text' => $product_code,
            'icon' => NULL,
                // isset($item->valueObject->Quantity) ? $item->valueObject->Quantity->valueNumber : ''
                // . ' @ ' .
                // isset($item->valueObject->Price) ? $item->valueObject->Price->valueNumber : ''
                // . ' = ' .
                // isset($item->valueObject->TotalPrice) ? money($item->valueObject->TotalPrice->valueNumber) : '',
            // 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z'
            ],
        ];
    @endphp
    <x-lists.search_li
        {{-- :basic=true --}}
        :line_title="$desc"
        :line_details="$line_details"
        >
    </x-lists.search_li>
@endif
