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
    id INT(11) PRIMARY KEY auto_increment,
    id_usuario  INT(11),
    id_grupo INT(11),
    permissao ENUM('dono','administrador', 'participante') NOT NULL DEFAULT 'participante',
    status tinyint(0) NOT NULL DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME
) ENGINE = INNODB;

ALTER TABLE `grupo_usuario` ADD INDEX `fk_grupo_usuario_idx` (`id_grupo` ASC);
ALTER TABLE `grupo_usuario` ADD CONSTRAINT `fk_grupo_usuario`
  FOREIGN KEY (`id_grupo`) REFERENCES `amigo_x`.`grupo` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;
