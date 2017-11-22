CREATE TABLE usuario (
	id INT(11) PRIMARY KEY  auto_increment,
    nome VARCHAR(100),
    email VARCHAR(100),
    senha CHAR(60),
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE KEY uniq_email(email)
) ENGINE = INNODB;

CREATE TABLE grupo (
	id INT(11) PRIMARY KEY  auto_increment,
    nome VARCHAR(100),
    created_at DATETIME,
    updated_at DATETIME
) ENGINE = INNODB;

CREATE TABLE grupo_usuario (
  id INT(11) PRIMARY KEY  auto_increment,
  id_grupo int(11) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  permissao enum('dono','administrador','participante') NOT NULL DEFAULT 'participante',
  status tinyint(4) NOT NULL DEFAULT '0',
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  KEY fk_grupo_usuario_idx (id_grupo),
  CONSTRAINT fk_grupo_usuario FOREIGN KEY (id_grupo) REFERENCES grupo (id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE sessao (
    id INT(11) PRIMARY KEY auto_increment,
    id_usuario INT(11) NOT NULL DEFAULT 0,
    descricao VARCHAR(100),
    local VARCHAR(150),
    data DATETIME,
    status tinyint(1) DEFAULT 1,
    obs VARCHAR(255),
    created_at DATETIME,
    updated_at DATETIME
) ENGINE = INNODB;

CREATE TABLE sessao_usuario (
    id INT(11) PRIMARY KEY auto_increment,
    id_sessao INT(11),
    id_usuario INT(11),
    created_at DATETIME,
    updated_at DATETIME
) ENGINE = INNODB;


CREATE TABLE sessao_sorteio (
    id INT(11) PRIMARY KEY auto_increment,
    id_sessao INT(11),
    id_usuario INT(11),
    id_amigo_secreto INT(11),
    created_at DATETIME,
    updated_at DATETIME
) ENGINE = INNODB;

CREATE TABLE produtos (
    id INT(11) PRIMARY KEY auto_increment,
    titulo VARCHAR(100),
    img VARCHAR(60),
    valor DECIMAL(9,2)
) ENGINE = INNODB;


INSERT INTO `produtos` VALUES 
(1,'Caneca super mario','caneca_mario.jpg',25.00),
(2,'Colar camera','colar_camera.jpg',50.00),
(3,'Colar oração Zelda','colar_zelda.jpg',50.00),
(4,'Boneco Homen de Ferro','homen_de_ferro.jpg',55.00),
(5,'Boneco Yoda','yoda.jpg',60.00);