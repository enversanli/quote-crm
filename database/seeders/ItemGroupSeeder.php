<?php

namespace Database\Seeders;

use App\Models\ItemGroup;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class ItemGroupSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'İçecekler',
                'items' => [
                    ['name' => 'Fritz  0,33 Kola',                      'unit' => 'L',          'price' => 18.99],
                    ['name' => 'Fritz-0,33 Kola Orange',                'unit' => 'L',          'price' => 18.99],
                    ['name' => 'Fritz-0,33 Kola 0. Zuck.',              'unit' => 'L',          'price' => 18.99],
                    ['name' => 'Gerolst. Still 0.25',                   'unit' => 'L',          'price' => 12.49],
                    ['name' => 'Gerolst. Classic 0.25',                 'unit' => 'L',          'price' => 12.49],
                    ['name' => 'Bauer Orange - 0.2',                    'unit' => 'L',          'price' => 21.49],
                    ['name' => 'Bauer Apfel - 0.2',                     'unit' => 'L',          'price' => 16.99],
                    ['name' => 'Fritz Kola  0,33 Honigmelone',          'unit' => 'L',          'price' => 18.99],
                    ['name' => 'Fritz Kola  0,33 Apfel-Kirsch-Holunder','unit' => 'L',          'price' => 18.99],
                ],
            ],
            [
                'name' => 'Mutfak, Tuvalet ve Diğer',
                'items' => [
                    ['name' => 'Papiertuch',                            'unit' => 'pcs',        'price' => null],
                    ['name' => 'Toilettenpapier',                       'unit' => 'pcs',        'price' => null],
                    ['name' => 'Filterkaffee',                          'unit' => 'pcs',        'price' => null],
                    ['name' => 'Türkischer Tee',                        'unit' => '',           'price' => null],
                    ['name' => 'Normale Milch',                         'unit' => 'pcs',        'price' => null],
                    ['name' => 'Toilettengeruch - Koku',                'unit' => 'pcs',        'price' => null],
                    ['name' => 'Flüssigseife - Sıvı Sabun',            'unit' => 'pcs',        'price' => null],
                    ['name' => 'Tahta Çay Kaşığı',                     'unit' => 'pcs',        'price' => null],
                ],
            ],
            [
                'name' => 'Event Hub - Dienstleistungen',
                'items' => [
                    ['name' => 'Raummiete',                             'unit' => 'std',        'price' => 117.00],
                    ['name' => 'Getränkepauschale p.P.',                'unit' => 'pro person', 'price' => 11.50],
                    ['name' => 'Reinigung',                             'unit' => '',           'price' => 100.00],
                    ['name' => 'Halbe belegte Brötchen',                'unit' => 'stück',      'price' => 4.50],
                    ['name' => 'Wasser (Sprudel, Normal)',              'unit' => 'stück',      'price' => 1.50],
                    ['name' => 'Fritz Kola (Orange, ohne Sugar oder Classic)', 'unit' => 'stück', 'price' => 2.50],
                    ['name' => 'Bauer Orangens & Apfels, 0.20 ',       'unit' => 'stück',      'price' => 2.00],
                    ['name' => 'Filterkaffee',                          'unit' => '',           'price' => null],
                    ['name' => 'Hauptgericht',                          'unit' => 'pro person', 'price' => 17.90],
                    ['name' => 'Lautsprecher mit Mikrofonen',           'unit' => 'tag',        'price' => 29.99],
                    ['name' => 'Snacks',                                'unit' => 'pro',        'price' => 2.50],
                    ['name' => 'Tisch und Stuhl',                       'unit' => 'stück',      'price' => 2.00],
                    ['name' => 'Flipcharts',                            'unit' => '',           'price' => 14.99],
                    ['name' => 'Beamer',                                'unit' => 'pro tag',    'price' => 14.99],
                ],
            ],
        ];

        foreach ($data as $groupData) {
            $group = ItemGroup::firstOrCreate(['name' => $groupData['name']]);

            foreach ($groupData['items'] as $item) {
                OrderItem::firstOrCreate(
                    ['item_group_id' => $group->id, 'name' => $item['name']],
                    ['unit' => $item['unit'], 'price' => $item['price']]
                );
            }
        }
    }
}