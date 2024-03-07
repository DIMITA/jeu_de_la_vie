pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                // Récupération du code depuis GitHub
                git 'https://github.com/DIMITA/jeu_de_la_vie.git'
            }
        }
        stage('Run QA tests') {
            steps {
                // Exécution des tests de qualité
                sh 'phpunit'
                // Autres tests QA si nécessaire
            }
        }

        stage('Deploy applications') {
            steps {
                // Déploiement de l'application PHP sur un port spécifique
                sh 'php -S localhost:3333 -t ./db_access &'

                // Déploiement de l'application qui communique via des sockets sur un autre port spécifique
                sh 'php -q localhost:8088 -t ./sockets &'
            }
        }
    }
}
