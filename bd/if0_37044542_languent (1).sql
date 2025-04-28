-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 29/04/2025 às 01:29
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_37044542_languent`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_lingua`
--

CREATE TABLE `tb_lingua` (
  `id_lingua` int(11) NOT NULL,
  `lingua` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tb_lingua`
--

INSERT INTO `tb_lingua` (`id_lingua`, `lingua`) VALUES
(1, 'ingles '),
(2, 'espanhol'),
(3, 'italiano'),
(4, 'frances');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_preferencias`
--

CREATE TABLE `tb_preferencias` (
  `id_preferencia` int(11) NOT NULL,
  `preferencia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tb_preferencias`
--

INSERT INTO `tb_preferencias` (`id_preferencia`, `preferencia`) VALUES
(1, 'sports'),
(2, 'movies'),
(3, 'music'),
(4, 'technology'),
(5, 'gastronomy'),
(6, 'literature'),
(7, 'art');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_usuario`
--

CREATE TABLE `tb_usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `senha` varchar(256) NOT NULL,
  `email` varchar(50) NOT NULL,
  `id_lingua` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tb_usuario`
--

INSERT INTO `tb_usuario` (`id_usuario`, `nome`, `senha`, `email`, `id_lingua`) VALUES
(1, 'teste', 'teste123', 'teste@gmail.com', 1),
(2, 'GustavoTeste', 'teste123456', 'gustavo.teste@gmail.com', 1),
(3, 'GigicaSB', 'Paulo123', 'paulo.israel@sempreceub.com', 1),
(4, 'Claudio', 'claudio123', 'claudio@gmail.com', 1),
(5, 'er', '1234', 'esadyft@gmail.com', NULL),
(6, 'Eric', '1234', 'ericsena726@gmail.com', NULL),
(7, 'Eric', '1234', 'ericsena726@gmail.com', NULL),
(10, 'teste1', 'teste1', 'teste1@gmail.com', 4),
(13, 'teste2', 'teste123', 'teste2@gmail.com', 4),
(18, 'Carlos ', 'SENHA123', 'carlosmota.ti@gmail.com', 1),
(19, 'valdemir', 'senha123', 'valdemir@gmail.com', 1),
(20, 'Paulo ', '2121', 'anderline360321@gmail.com', NULL),
(21, 'Paulo ', '2121', 'anderline360321@gmail.com', NULL),
(22, 'Geovane Saraiva Barros ', 'Languentgeo@159', 'geovanesaraivabarros@gmail.com', NULL),
(23, 'Geovane Saraiva Barros ', 'Languentgeo@159', 'geovanesaraivabarros@gmail.com', NULL),
(24, 'Geovane Saraiva Barros ', 'Languentgeo@159', 'geovanesaraivabarros@gmail.com', NULL),
(25, 'Geovane Saraiva Barros ', 'Languentgeo@159', 'geovanesaraivabarros@gmail.com', NULL),
(26, 'Geovane Saraiva Barros ', 'Ingles123', 'geovanesaraivabarros@gmail.com', 1),
(27, 'GigicaSB', 'Ceubgeo159', 'geovane.sb@Sempreceub.com', NULL),
(28, 'GigicaSB', 'Ceubgeo159', 'geovane.sb@sempreceub.com', NULL),
(29, 'GigicaSB', 'Ceub123', 'geovane.sb@Sempreceub.com', NULL),
(30, 'GigicaSB', 'Ceub@159', 'geovane.sb@sempreceub.com', NULL),
(31, 'GigicaSB', 'Ceub@15915', 'geovane.sb@sempreceub.com', NULL),
(32, 'GigicaSB', 'Ceub@159159', 'geovane.sb@sempreceub.com', NULL),
(33, 'GigicaSB', 'Ceub@159159159', 'geovane.sb@sempreceub.com', NULL),
(34, 'GigicaSB', 'gigica', 'geovane.sb@sempreceub.com', NULL),
(35, 'GigicaSB', 'gigica123', 'geovane.sb@sempreceub.com', NULL),
(36, 'GigicaSB', 'gigica@123', 'geovane.sb@sempreceub.com', NULL),
(37, 'GigicaSB', 'Gigica@123', 'geovane.sb@sempreceub.com', NULL),
(38, 'GigicaSB', '12345678', 'geovane.sb@sempreceub.com', NULL),
(39, 'GigicaSB', 'Sasque123', 'geovane.sb@sempreceub.com', NULL),
(40, 'GigicaSB', 'Sasque123', 'geovane.sb@sempreceubcom', NULL),
(41, 'GigicaSB', 'Sasque123', 'geovane.sb@fodase.com', NULL),
(42, 'GustavoTeste', 'teste', 'gustavoteste@gmail.com', NULL),
(43, 'GigicaSB', 'geo', 'geovane.sb@sempreceub.com', 1),
(44, 'GigicaSB', 'geo', 'a@sempreceub.com', NULL),
(45, 'GigicaSB', 'geo', 'a@sempreceub', NULL),
(46, 'GigicaSB', 'geo', 'a@sempreceub', NULL),
(47, 'Paulo', 's>#yES_-6uN3XM;', 'Paulo.israel@sempreceub.com', 1),
(48, 'AAAAAa', 's>#yES_-6uN3XM;', 'Felipe@gmail.com', 1),
(49, 'Eduardo', '1234567', 'jose@a', NULL),
(50, 'Ellen', 'geovane', 'ellenwendiaaraujo@icloud.com', 1),
(51, 'Teste3', '$2y$10$GJ.Of.ZMjrb5EsMW3.zz4OWpSmkqpf8b9DpQMfNIks8eG6WTCugXy', 'teste3@gmail.com', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_usuario_preferencia`
--

CREATE TABLE `tb_usuario_preferencia` (
  `id_usuario` int(11) NOT NULL,
  `id_preferencia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tb_usuario_preferencia`
--

INSERT INTO `tb_usuario_preferencia` (`id_usuario`, `id_preferencia`) VALUES
(1, 2),
(1, 3),
(1, 4),
(10, 1),
(10, 3),
(10, 4),
(13, 1),
(13, 4),
(13, 7),
(18, 1),
(18, 3),
(18, 5),
(19, 1),
(19, 4),
(19, 5),
(26, 2),
(26, 3),
(26, 4),
(43, 2),
(43, 4),
(43, 5),
(47, 2),
(47, 3),
(47, 4),
(48, 3),
(48, 4),
(48, 5),
(50, 2),
(50, 3),
(50, 6),
(51, 1),
(51, 2),
(51, 3);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tb_lingua`
--
ALTER TABLE `tb_lingua`
  ADD PRIMARY KEY (`id_lingua`);

--
-- Índices de tabela `tb_preferencias`
--
ALTER TABLE `tb_preferencias`
  ADD PRIMARY KEY (`id_preferencia`);

--
-- Índices de tabela `tb_usuario`
--
ALTER TABLE `tb_usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `idx_nome` (`nome`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `fk_usuario_lingua` (`id_lingua`);

--
-- Índices de tabela `tb_usuario_preferencia`
--
ALTER TABLE `tb_usuario_preferencia`
  ADD PRIMARY KEY (`id_usuario`,`id_preferencia`),
  ADD KEY `id_preferencia` (`id_preferencia`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_lingua`
--
ALTER TABLE `tb_lingua`
  MODIFY `id_lingua` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tb_usuario`
--
ALTER TABLE `tb_usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_usuario`
--
ALTER TABLE `tb_usuario`
  ADD CONSTRAINT `fk_usuario_lingua` FOREIGN KEY (`id_lingua`) REFERENCES `tb_lingua` (`id_lingua`);

--
-- Restrições para tabelas `tb_usuario_preferencia`
--
ALTER TABLE `tb_usuario_preferencia`
  ADD CONSTRAINT `tb_usuario_preferencia_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuario` (`id_usuario`),
  ADD CONSTRAINT `tb_usuario_preferencia_ibfk_2` FOREIGN KEY (`id_preferencia`) REFERENCES `tb_preferencias` (`id_preferencia`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
