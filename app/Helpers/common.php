<?php

function generateSku(string $alias, string $type, string $modelClass): string
    {
        do {
            $sku = $type . '-' . $alias . '-' . strtotime(date()) . '-' . Str::lower(Str::random(3));
        } while ($modelClass::where('sku', $sku)->exists());
        return $sku;
    }