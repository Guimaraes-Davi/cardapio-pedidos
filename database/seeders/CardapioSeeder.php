<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CardapioSeeder extends Seeder
{
    public function run(): void
    {
        $cardapio = [
            'Lanches' => [
                ['X-Burger', 'Pão, hambúrguer 150g, queijo e molho da casa', 2490],
                ['X-Salada', 'Hambúrguer 150g, queijo, alface, tomate e maionese', 2790],
                ['X-Bacon', 'Hambúrguer 150g, queijo, bacon crocante e cebola caramelizada', 3290],
                ['Cheddar Duplo', 'Dois hambúrgueres 120g com cheddar cremoso', 3690],
                ['Frango Crispy', 'Filé de frango empanado, alface e molho especial', 2890],
            ],
            'Pizzas' => [
                ['Margherita', 'Molho de tomate, mussarela e manjericão', 4990],
                ['Calabresa', 'Calabresa fatiada, cebola e azeitonas', 4790],
                ['Quatro Queijos', 'Mussarela, provolone, gorgonzola e parmesão', 5490],
                ['Portuguesa', 'Presunto, ovo, cebola, ervilha e mussarela', 5290],
                ['Frango com Catupiry', 'Frango desfiado e catupiry original', 5190],
            ],
            'Bebidas' => [
                ['Refrigerante lata', 'Lata 350ml', 690],
                ['Suco natural', 'Laranja, limão ou maracujá, 500ml', 990],
                ['Água mineral', 'Garrafa 500ml', 450],
                ['Chá gelado', 'Pêssego ou limão, 450ml', 790],
                ['Cerveja long neck', 'Garrafa 355ml', 1190],
            ],
        ];

        foreach ($cardapio as $nomeCategoria => $produtos) {
            $categoria = Categoria::create(['nome' => $nomeCategoria]);

            foreach ($produtos as [$nome, $descricao, $preco]) {
                $categoria->produtos()->create([
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'preco_centavos' => $preco,
                ]);
            }
        }
    }
}
