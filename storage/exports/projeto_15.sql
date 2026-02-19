-- Export parcial do projeto 15
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `projetos`
--
-- WHERE:  id=15

LOCK TABLES `projetos` WRITE;
/*!40000 ALTER TABLE `projetos` DISABLE KEYS */;
INSERT INTO `projetos` VALUES (15,'Cultura do Saber - PNAB',NULL,'2026-01-01','2027-04-01',NULL,'em_execucao',NULL,'2026-02-18 16:28:45','2026-02-18 17:08:11',NULL);
/*!40000 ALTER TABLE `projetos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `metas`
--
-- WHERE:  projeto_id=15

LOCK TABLES `metas` WRITE;
/*!40000 ALTER TABLE `metas` DISABLE KEYS */;
INSERT INTO `metas` VALUES (29,15,NULL,'Formação e Educação Cultural',NULL,NULL,'a_iniciar','2026-02-18 16:48:30','2026-02-18 16:48:30'),(30,15,NULL,'Produção de Mostra Artística/Cultural',NULL,NULL,'a_iniciar','2026-02-18 17:03:13','2026-02-18 17:03:13'),(31,15,NULL,'Registro e divulgação',NULL,NULL,'a_iniciar','2026-02-18 17:09:58','2026-02-18 17:09:58');
/*!40000 ALTER TABLE `metas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `tarefas`
--
-- WHERE:  meta_id IN (SELECT id FROM metas WHERE projeto_id=15)

LOCK TABLES `tarefas` WRITE;
/*!40000 ALTER TABLE `tarefas` DISABLE KEYS */;
INSERT INTO `tarefas` VALUES (198,29,NULL,'Conferir fotos das atividades de balé, música, circo e artes visuais no Drive.',NULL,NULL,NULL,NULL,'2026-02-10','devolvido','Conferir no drive do marketing se as fotos das oficinas foram inseridas.',NULL,0,NULL,NULL,'[Devolvido por Bruno Veloso] Presta mais atenção','2026-02-18 16:53:11','2026-02-18 17:25:25'),(199,29,NULL,'Conferir 45 inscritos em circo, 40 em artes visuais, 48 em balé e 20 iniciação musical',NULL,NULL,NULL,NULL,'2026-02-10','a_iniciar','Olhar o sistema de atividades e conferir o número de vagas ofertadas e preenchidas em cada modalidade no polo Bandinha',NULL,0,NULL,NULL,NULL,'2026-02-18 16:57:16','2026-02-18 16:57:16'),(200,29,NULL,'Demonstrar medidas de acessibilidade arquitetônicas e treinamento de equipe',NULL,NULL,NULL,NULL,'2026-02-28','a_iniciar','Solicitar fotos de treinamento de equipe e acessibilidade arquitetônica.',NULL,0,NULL,NULL,NULL,'2026-02-18 17:01:39','2026-02-18 17:01:39'),(207,29,NULL,'Relato mensal das atividades do projeto',NULL,NULL,NULL,NULL,'2026-02-10','a_iniciar','Incluir relato no drive',NULL,0,NULL,NULL,NULL,'2026-02-18 17:31:05','2026-02-18 17:31:05'),(208,29,NULL,'Aplicar avaliação autodeclaratória dos alunos',NULL,NULL,NULL,NULL,'2026-11-30','a_iniciar','Será orientado pelo diretor de projetos',NULL,0,NULL,NULL,NULL,'2026-02-18 17:35:32','2026-02-18 17:35:32'),(201,30,NULL,'Inserir fotos da exposição das atividades de artes, música, circo e balé na mostra cultural.',NULL,NULL,NULL,NULL,'2026-11-30','a_iniciar','Registros fotográficos do evento.',NULL,0,NULL,NULL,NULL,'2026-02-18 17:04:28','2026-02-18 17:04:28'),(202,30,NULL,'Demonstrar ações de acessibilidade',NULL,NULL,NULL,NULL,'2026-11-30','a_iniciar','Estão previstas no projeto medidas de acessibilidade arquitetônica: rampas, áreas planas para circulação\ne vagas reservadas à frente do palco.\nDentro das medidas de acessibilidade comunicacional: materiais audiovisuais legendados; Linguagem\nsimples e objetiva na comunicação escrita e falada; Materiais em formato de leitura fácil com frases\ncurtas e ilustrações.\nDentro das medidas de acessibilidade atitudinal: treinamento de equipes para atendimento inclusivo;\nSensibilização sobre diferentes tipos de deficiência e formas de comunicação acessível; Programação\nde espetáculos, exposições e eventos que abordem a diversidade e a inclusão.\n',NULL,0,NULL,NULL,NULL,'2026-02-18 17:05:59','2026-02-18 17:05:59'),(203,30,NULL,'Beneficiar 200 pessoas com o evento de 07 comunidades diferentes',NULL,NULL,NULL,NULL,'2026-11-30','a_iniciar','Passar lista de presença com a comunidade de cada beneficiário.',NULL,0,NULL,NULL,NULL,'2026-02-18 17:06:43','2026-02-18 17:06:43'),(204,31,NULL,'Divulgar a oferta e a realização dos cursos através de mídias digitais, cartazes e parcerias locais.',NULL,NULL,NULL,NULL,'2026-03-15','a_iniciar','Postagens no instagram e impressão de cartazes.',NULL,0,NULL,NULL,NULL,'2026-02-18 17:11:00','2026-02-18 17:11:00'),(205,31,NULL,'Divulgar e mobilizar a comunidade para o Festival Comunitário por meio de mídias digitais, cartazes e convites.',NULL,NULL,NULL,NULL,'2026-10-30','a_iniciar','- Criação de materiais gráficos e vídeos para as redes sociais, divulgando o evento e seu resultado;\n- Impressão e distribuição de convites para lideranças e parceiros;\n- Divulgação através de cartazes impressos;\n- Divulgação em parceria com as associações locais, escolas e Secretaria Municipal de Cultura.',NULL,0,NULL,NULL,NULL,'2026-02-18 17:12:15','2026-02-18 17:12:15'),(206,31,NULL,'Ações de acessibilidade na divulgação',NULL,NULL,NULL,NULL,'2026-10-30','a_iniciar','Materiais audiovisuais legendados; Contraste e ampliação de fontes em materia de divulgação;\nLinguagem simples e objetiva na comunicação escrita e falada; Materiais em formato de leitura fácil com\nfrases curtas e ilustrações. Utilização de audiodescrição para atender pessoas com deficiência auditiva e\nvisual; Utilização de elementos gráficos e ícones que reforcem as informações de forma visual, tornando\nos materiais mais intuitivos e de fácil interpretação.',NULL,0,NULL,NULL,NULL,'2026-02-18 17:13:00','2026-02-18 17:13:00');
/*!40000 ALTER TABLE `tarefas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `historico_tarefas`
--
-- WHERE:  tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))

LOCK TABLES `historico_tarefas` WRITE;
/*!40000 ALTER TABLE `historico_tarefas` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_tarefas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `tarefa_user`
--
-- WHERE:  tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))

LOCK TABLES `tarefa_user` WRITE;
/*!40000 ALTER TABLE `tarefa_user` DISABLE KEYS */;
INSERT INTO `tarefa_user` VALUES (6,198,9,'2026-02-18 16:53:11','2026-02-18 16:53:11'),(7,199,9,'2026-02-18 16:57:16','2026-02-18 16:57:16'),(8,200,9,'2026-02-18 17:01:39','2026-02-18 17:01:39'),(15,207,5,'2026-02-18 17:31:05','2026-02-18 17:31:05'),(9,201,9,'2026-02-18 17:04:28','2026-02-18 17:04:28'),(10,202,9,'2026-02-18 17:05:59','2026-02-18 17:05:59'),(11,203,9,'2026-02-18 17:06:43','2026-02-18 17:06:43'),(12,204,9,'2026-02-18 17:11:00','2026-02-18 17:11:00'),(13,205,9,'2026-02-18 17:12:15','2026-02-18 17:12:15'),(14,206,9,'2026-02-18 17:13:00','2026-02-18 17:13:00');
/*!40000 ALTER TABLE `tarefa_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `tarefa_ocorrencias`
--
-- WHERE:  tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))

LOCK TABLES `tarefa_ocorrencias` WRITE;
/*!40000 ALTER TABLE `tarefa_ocorrencias` DISABLE KEYS */;
INSERT INTO `tarefa_ocorrencias` VALUES (25,198,'2026-02-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(26,198,'2026-03-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(27,198,'2026-04-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(28,198,'2026-05-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(29,198,'2026-06-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(30,198,'2026-07-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(31,198,'2026-08-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(32,198,'2026-09-10','2026-02-18 16:53:11','2026-02-18 16:53:11'),(33,199,'2026-02-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(34,199,'2026-03-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(35,199,'2026-04-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(36,199,'2026-05-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(37,199,'2026-06-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(38,199,'2026-07-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(39,199,'2026-08-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(40,199,'2026-09-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(41,199,'2026-10-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(42,199,'2026-11-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(43,199,'2026-12-10','2026-02-18 16:57:16','2026-02-18 16:57:16'),(44,207,'2026-02-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(45,207,'2026-03-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(46,207,'2026-04-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(47,207,'2026-05-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(48,207,'2026-06-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(49,207,'2026-07-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(50,207,'2026-08-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(51,207,'2026-09-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(52,207,'2026-10-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(53,207,'2026-11-10','2026-02-18 17:31:05','2026-02-18 17:31:05'),(54,207,'2026-12-10','2026-02-18 17:31:05','2026-02-18 17:31:05');
/*!40000 ALTER TABLE `tarefa_ocorrencias` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `tarefa_realizacoes`
--
-- WHERE:  tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))

LOCK TABLES `tarefa_realizacoes` WRITE;
/*!40000 ALTER TABLE `tarefa_realizacoes` DISABLE KEYS */;
INSERT INTO `tarefa_realizacoes` VALUES (4,198,2,'asdasdasdasdasdsa','2026-02-18 17:23:54','2026-02-18 17:23:54');
/*!40000 ALTER TABLE `tarefa_realizacoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `projeto_polo`
--
-- WHERE:  projeto_id=15

LOCK TABLES `projeto_polo` WRITE;
/*!40000 ALTER TABLE `projeto_polo` DISABLE KEYS */;
INSERT INTO `projeto_polo` VALUES (28,15,1,'2026-02-18 16:28:45','2026-02-18 16:28:45');
/*!40000 ALTER TABLE `projeto_polo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `polos`
--
-- WHERE:  id IN (SELECT polo_id FROM projeto_polo WHERE projeto_id=15)

LOCK TABLES `polos` WRITE;
/*!40000 ALTER TABLE `polos` DISABLE KEYS */;
INSERT INTO `polos` VALUES (1,'Bandinha',1,0,'2026-02-13 01:59:22','2026-02-13 01:59:22');
/*!40000 ALTER TABLE `polos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `projeto_financiador`
--
-- WHERE:  projeto_id=15

LOCK TABLES `projeto_financiador` WRITE;
/*!40000 ALTER TABLE `projeto_financiador` DISABLE KEYS */;
INSERT INTO `projeto_financiador` VALUES (16,15,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 16:28:45','2026-02-18 16:28:45');
/*!40000 ALTER TABLE `projeto_financiador` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `financiadores`
--
-- WHERE:  id IN (SELECT financiador_id FROM projeto_financiador WHERE projeto_id=15)

LOCK TABLES `financiadores` WRITE;
/*!40000 ALTER TABLE `financiadores` DISABLE KEYS */;
INSERT INTO `financiadores` VALUES (5,'Secretaria de Cultura de MG','publico',NULL,NULL,NULL,'2026-02-13 01:59:22','2026-02-13 13:48:30');
/*!40000 ALTER TABLE `financiadores` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `etapas_prestacao`
--
-- WHERE:  projeto_id=15

LOCK TABLES `etapas_prestacao` WRITE;
/*!40000 ALTER TABLE `etapas_prestacao` DISABLE KEYS */;
INSERT INTO `etapas_prestacao` VALUES (70,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-02-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(71,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-03-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(72,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-04-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(73,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-05-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(74,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-06-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(75,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-07-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(76,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-08-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(77,NULL,NULL,'Publicação no site sobre os valores pagos de salário','interna',15,'financeira','2026-09-10',NULL,NULL,'pendente',NULL,'Repassar para o email do relatório de gastos para publicação no site.','2026-02-18 16:34:50','2026-02-18 16:34:50',NULL,NULL),(79,NULL,NULL,'Relatório final','interna',15,'qualitativa','2027-02-28',NULL,NULL,'pendente',NULL,' 70% dos alunos inscritos formados nos cursos de formação artística – circo, balé, artes visuais e iniciação musical.\n 0% de abandono escolar entre os alunos inscritos no projeto;\n 0% de reprovação escolar entre os alunos inscritos no projeto;\n Realização de 01 Festival Cultural Comunitário com a participação mínima de 200 pessoas da comunidade;\n 80% dos alunos que declaram ter melhorado os espaços de acesso à cultura após a execução do projeto','2026-02-18 17:18:31','2026-02-18 17:18:31',NULL,NULL),(80,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-02-15',NULL,NULL,'em_analise',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:39:14',NULL,NULL),(81,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-03-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(82,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-04-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(83,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-05-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(84,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-06-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(85,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-07-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(86,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-08-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(87,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-09-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(88,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-10-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(89,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-11-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(90,NULL,NULL,'Conferir fotos e relatos do projeto','interna',15,'qualitativa','2026-12-15',NULL,NULL,'pendente',NULL,'Conferir relatos e fotos enviadas no drive.','2026-02-18 17:33:28','2026-02-18 17:33:28',NULL,NULL),(91,16,NULL,'Prestação de contas final','financiador',15,'qualitativa','2027-02-28',NULL,NULL,'pendente',NULL,NULL,'2026-02-18 17:34:15','2026-02-18 17:34:15',NULL,NULL);
/*!40000 ALTER TABLE `etapas_prestacao` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `prestacao_realizacoes`
--
-- WHERE:  etapa_prestacao_id IN (SELECT id FROM etapas_prestacao WHERE projeto_id=15)

LOCK TABLES `prestacao_realizacoes` WRITE;
/*!40000 ALTER TABLE `prestacao_realizacoes` DISABLE KEYS */;
INSERT INTO `prestacao_realizacoes` VALUES (6,80,9,'dsadasdsadasdsa','2026-02-18 17:39:14','2026-02-18 17:39:14');
/*!40000 ALTER TABLE `prestacao_realizacoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: gestaoprojeto
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `users`
--
-- WHERE:  id IN (
--   SELECT DISTINCT u_id FROM (
--     SELECT responsavel_user_id AS u_id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15)
--     UNION
--     SELECT validado_por AS u_id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15) AND validado_por IS NOT NULL
--     UNION
--     SELECT user_id AS u_id FROM historico_tarefas WHERE tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))
--     UNION
--     SELECT user_id AS u_id FROM tarefa_user WHERE tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))
--     UNION
--     SELECT user_id AS u_id FROM tarefa_realizacoes WHERE tarefa_id IN (SELECT id FROM tarefas WHERE meta_id IN (SELECT id FROM metas WHERE projeto_id=15))
--     UNION
--     SELECT validado_por AS u_id FROM etapas_prestacao WHERE projeto_id=15 AND validado_por IS NOT NULL
--     UNION
--     SELECT user_id AS u_id FROM prestacao_realizacoes WHERE etapa_prestacao_id IN (SELECT id FROM etapas_prestacao WHERE projeto_id=15)
--   ) t WHERE u_id IS NOT NULL
-- )

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Thiago','thiago@sementesdovale.org',NULL,'$2y$12$zTkiLTpzJLlu8PbqrYPx4u8MbHOPTjM8qBFreVDkfKgVJ.bX/ngpK','super_admin',NULL,'2026-02-13 22:05:28','2026-02-18 16:50:48',NULL,1),(5,'Cris','cristiane@sementesdovale.org',NULL,'$2y$12$25n0DpsikunF5DPo6iP8NuNK2oSI9/PCLDEF37usDbN5lT98Mjyr.','coordenador_polo',NULL,'2026-02-16 17:55:07','2026-02-16 17:55:07',NULL,1),(9,'Bruno Veloso','bruno@sementesdovale.org',NULL,'$2y$12$tOJ2cSFJGFCuDnKhhwDtn.2sxmuRUlvJVlsqvaQr/S0rIEdvrBJ/u','diretor_projetos',NULL,'2026-02-18 16:51:03','2026-02-18 16:51:03',NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-18 14:40:10
COMMIT;
SET FOREIGN_KEY_CHECKS=1;
