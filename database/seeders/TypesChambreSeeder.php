<?php

namespace Database\Seeders;

use App\Models\TypesChambre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypesChambreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "name" => "Chambre simple",
                "capacity" => "1 personne",
                "features" => "1 lit simple, idéale pour les voyageurs seuls",
            ],
            [
                "name" => "Chambre double",
                "capacity" => "2 personnes",
                "features" => "1 lit double (queen ou king) ou 2 lits simples",
            ],
            [
                "name" => "Chambre twin",
                "capacity" => "2 personnes",
                "features" => "2 lits simples séparés",
            ],
            [
                "name" => "Chambre Familiale",
                "capacity" => "3 à 6 personnes",
                "features" => "Adaptée pour les familles, généralement composée de plusieurs lits ou d'un lit double avec lits superposés.",
            ],
            [
                "name" => "Junior Suite",
                "capacity" => "2 personnes ou plus",
                "features" => "Petite suite avec une zone salon dans la même pièce que la chambre à coucher.",
            ],
            [
                "name" => "Master Suite",
                "capacity" => "2 personnes ou plus",
                "features" => "Grande suite avec plusieurs pièces, souvent équipée de cuisines, de salons et d’autres commodités.",
            ],
            [
                "name" => "Duplex",
                "capacity" => "2 personnes ou plus",
                "features" => "Chambre sur deux niveaux reliés par un escalier intérieur, souvent avec un salon à l'étage inférieur et une chambre à coucher à l'étage supérieur",
            ],
            [
                "name" => "Penthouse",
                "capacity" => "Variable",
                "features" => "Chambre située à l'étage supérieur de l'hôtel, offrant des vues panoramiques et des équipements de luxe.",
            ]
        ];
        foreach ($data as $room_type){
            TypesChambre::create($room_type);
        }
    }
}
