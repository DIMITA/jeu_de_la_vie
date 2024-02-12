<?php

// Fonctions pour générer et calculer la grille

function genererGrilleAleatoire($largeur, $hauteur)
{
    $grille = array();
    for ($y = 0; $y < $hauteur; $y++) {
        $grille[$y] = array();
        for ($x = 0; $x < $largeur; $x++) {
            $grille[$y][$x] = rand(0, 1);
        }
    }
    return $grille;
}

function calculerGenerationSuivante($grille, $largeur, $hauteur)
{
    $nouvelleGrille = array();

    for ($y = 0; $y < $hauteur; $y++) {
        for ($x = 0; $x < $largeur; $x++) {
            $voisinsVivants = 0;

            // Parcourir les voisins autour de la cellule actuelle
            for ($dy = -1; $dy <= 1; $dy++) {
                for ($dx = -1; $dx <= 1; $dx++) {
                    // Ne pas inclure la cellule actuelle dans les voisins
                    if ($dx != 0 || $dy != 0) {
                        $voisinX = $x + $dx;
                        $voisinY = $y + $dy;
                        // Vérifier si le voisin est dans la grille et s'il est vivant
                        if ($voisinX >= 0 && $voisinX < $largeur && $voisinY >= 0 && $voisinY < $hauteur) {
                            $voisinsVivants += $grille[$voisinY][$voisinX];
                        }
                    }
                }
            }

            // Appliquer les règles du jeu de la vie
            if ($grille[$y][$x]) {
                if ($voisinsVivants == (int) $_GET["min"] || $voisinsVivants == (int) $_GET["max"]) {
                    $nouvelleGrille[$y][$x] = 1; // La cellule reste vivante
                } else {
                    $nouvelleGrille[$y][$x] = 0; // La cellule meurt
                }
            } else {
                if ($voisinsVivants == 3) {
                    $nouvelleGrille[$y][$x] = 1; // La cellule naît
                } else {
                    $nouvelleGrille[$y][$x] = 0; // La cellule reste morte
                }
            }
        }
    }

    return $nouvelleGrille;
}


// Paramètres de la grille
$largeur = (int) $_GET["size"];
$hauteur = (int) $_GET["size"];

// Récupérer la grille actuelle depuis une source (base de données, fichier, etc.)
// Dans cet exemple, nous utilisons une grille aléatoire pour la démonstration
$grille = genererGrilleAleatoire($largeur, $hauteur);

// Calculer la prochaine génération de la grille
$grille = calculerGenerationSuivante($grille, $largeur, $hauteur);

// Envoyer la grille au format JSON
header('Content-Type: application/json');
echo json_encode($grille);
