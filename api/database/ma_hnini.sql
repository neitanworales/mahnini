-- =========================================================
-- MA HNINI - ESQUEMA MYSQL V2
-- Sistema de Administración Comunitaria Multi-Comunidad
-- =========================================================

USE ma_hnini;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS persona_obligaciones;
DROP TABLE IF EXISTS reunion_participantes;
DROP TABLE IF EXISTS reuniones;
DROP TABLE IF EXISTS faena_participantes;
DROP TABLE IF EXISTS faenas;
DROP TABLE IF EXISTS pago_detalle;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS cargos;
DROP TABLE IF EXISTS obras;
DROP TABLE IF EXISTS conceptos_cargo;
DROP TABLE IF EXISTS hogar_personas;
DROP TABLE IF EXISTS hogares;
DROP TABLE IF EXISTS auth_tokens;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS personas;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS comunidades;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE comunidades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    municipio VARCHAR(120),
    estado VARCHAR(120),
    direccion VARCHAR(255),
    telefono VARCHAR(30),
    email VARCHAR(150),
    codigo_postal VARCHAR(10),
    descripcion TEXT,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Catalogos por comunidad para estandarizar barrio y seccion/sector de las personas.
CREATE TABLE barrios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_barrios_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    UNIQUE KEY uk_barrios_comunidad_nombre (comunidad_id, nombre),
    INDEX idx_barrios_comunidad (comunidad_id)
);

CREATE TABLE secciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_secciones_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    UNIQUE KEY uk_secciones_comunidad_nombre (comunidad_id, nombre),
    INDEX idx_secciones_comunidad (comunidad_id)
);

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (nombre, descripcion) VALUES
('SUPERADMIN', 'Administrador global de la plataforma'),
('ADMIN_COMUNIDAD', 'Administrador de una comunidad'),
('TESORERO', 'Gestión de cargos, pagos y reportes'),
('CAPTURISTA', 'Captura y consulta de información'),
('CONSULTA', 'Acceso de solo lectura');

CREATE TABLE personas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    numero_control VARCHAR(40) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100),
    apellido_materno VARCHAR(100),
    fecha_nacimiento DATE,
    telefono VARCHAR(30),
    email VARCHAR(150),
    direccion VARCHAR(255),
    barrio VARCHAR(120),
    seccion VARCHAR(120),
    fecha_alta DATE NULL,
    estatus ENUM('ACTIVO','INACTIVO','FALLECIDO','BAJA') NOT NULL DEFAULT 'ACTIVO',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_personas_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    UNIQUE KEY uk_persona_numero_control (comunidad_id, numero_control),
    INDEX idx_personas_comunidad (comunidad_id),
    INDEX idx_personas_nombre (nombre, apellido_paterno, apellido_materno),
    INDEX idx_personas_estatus (estatus)
);

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NULL,
    persona_id BIGINT UNSIGNED NULL,
    rol_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_usuarios_persona FOREIGN KEY (persona_id) REFERENCES personas(id),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles(id),
    UNIQUE KEY uk_usuarios_email (email),
    INDEX idx_usuarios_comunidad (comunidad_id),
    INDEX idx_usuarios_rol (rol_id)
);

-- Tokens de sesion (login) y de recuperacion de contrasena
CREATE TABLE auth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    token_type ENUM('AUTH','PASSWORD_RESET') NOT NULL DEFAULT 'AUTH',
    expires_at DATETIME NULL,
    revoked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auth_tokens_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_auth_tokens_token (token),
    INDEX idx_auth_tokens_usuario (usuario_id),
    INDEX idx_auth_tokens_type (token_type)
);

CREATE TABLE hogares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    clave VARCHAR(40) NOT NULL,
    direccion VARCHAR(255),
    responsable_persona_id BIGINT UNSIGNED NULL,
    estatus ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_hogares_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_hogares_responsable FOREIGN KEY (responsable_persona_id) REFERENCES personas(id),
    UNIQUE KEY uk_hogar_clave (comunidad_id, clave),
    INDEX idx_hogares_comunidad (comunidad_id)
);

CREATE TABLE hogar_personas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hogar_id BIGINT UNSIGNED NOT NULL,
    persona_id BIGINT UNSIGNED NOT NULL,
    parentesco VARCHAR(80),
    es_responsable BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hogar_personas_hogar FOREIGN KEY (hogar_id) REFERENCES hogares(id) ON DELETE CASCADE,
    CONSTRAINT fk_hogar_personas_persona FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_hogar_persona (hogar_id, persona_id)
);

CREATE TABLE conceptos_cargo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    tipo ENUM('SERVICIO','FAENA','REUNION','COOPERACION_ANUAL','COOPERACION_OBRA','SANCION','OTRO') NOT NULL DEFAULT 'OTRO',
    monto_default DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    periodicidad ENUM('UNICO','SEMANAL','MENSUAL','BIMESTRAL','TRIMESTRAL','SEMESTRAL','ANUAL','EVENTUAL') NOT NULL DEFAULT 'EVENTUAL',
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_conceptos_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    INDEX idx_conceptos_comunidad (comunidad_id),
    INDEX idx_conceptos_tipo (tipo)
);

CREATE TABLE obras (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(180) NOT NULL,
    descripcion TEXT,
    anio SMALLINT UNSIGNED NOT NULL,
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    monto_objetivo DECIMAL(14,2) NULL,
    estatus ENUM('PLANEADA','ACTIVA','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PLANEADA',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_obras_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    INDEX idx_obras_comunidad (comunidad_id),
    INDEX idx_obras_anio (anio),
    INDEX idx_obras_estatus (estatus)
);

CREATE TABLE cargos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    persona_id BIGINT UNSIGNED NULL,
    hogar_id BIGINT UNSIGNED NULL,
    concepto_id BIGINT UNSIGNED NOT NULL,
    obra_id BIGINT UNSIGNED NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    periodo VARCHAR(50),
    descripcion VARCHAR(255),
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NULL,
    monto DECIMAL(12,2) NOT NULL,
    saldo DECIMAL(12,2) NOT NULL,
    estatus ENUM('PENDIENTE','PARCIAL','PAGADO','CANCELADO','CONDONADO','VENCIDO') NOT NULL DEFAULT 'PENDIENTE',
    origen_tipo ENUM('MANUAL','FAENA','REUNION','COOPERACION_ANUAL','COOPERACION_OBRA','SERVICIO','OTRO') NOT NULL DEFAULT 'MANUAL',
    origen_id BIGINT UNSIGNED NULL,
    creado_por BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cargos_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_cargos_persona FOREIGN KEY (persona_id) REFERENCES personas(id),
    CONSTRAINT fk_cargos_hogar FOREIGN KEY (hogar_id) REFERENCES hogares(id),
    CONSTRAINT fk_cargos_concepto FOREIGN KEY (concepto_id) REFERENCES conceptos_cargo(id),
    CONSTRAINT fk_cargos_obra FOREIGN KEY (obra_id) REFERENCES obras(id),
    CONSTRAINT fk_cargos_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id),
    INDEX idx_cargos_comunidad (comunidad_id),
    INDEX idx_cargos_persona (persona_id),
    INDEX idx_cargos_hogar (hogar_id),
    INDEX idx_cargos_concepto (concepto_id),
    INDEX idx_cargos_obra (obra_id),
    INDEX idx_cargos_anio (anio),
    INDEX idx_cargos_estatus (estatus),
    INDEX idx_cargos_fecha (fecha_emision, fecha_vencimiento)
);

CREATE TABLE pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    persona_id BIGINT UNSIGNED NULL,
    hogar_id BIGINT UNSIGNED NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto_total DECIMAL(12,2) NOT NULL,
    metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','OTRO') NOT NULL DEFAULT 'EFECTIVO',
    referencia VARCHAR(120),
    observaciones TEXT,
    estatus ENUM('APLICADO','CANCELADO') NOT NULL DEFAULT 'APLICADO',
    usuario_registro_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagos_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_pagos_persona FOREIGN KEY (persona_id) REFERENCES personas(id),
    CONSTRAINT fk_pagos_hogar FOREIGN KEY (hogar_id) REFERENCES hogares(id),
    CONSTRAINT fk_pagos_usuario FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id),
    INDEX idx_pagos_comunidad (comunidad_id),
    INDEX idx_pagos_persona (persona_id),
    INDEX idx_pagos_hogar (hogar_id),
    INDEX idx_pagos_fecha (fecha)
);

CREATE TABLE pago_detalle (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pago_id BIGINT UNSIGNED NOT NULL,
    cargo_id BIGINT UNSIGNED NOT NULL,
    monto_aplicado DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pago_detalle_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE,
    CONSTRAINT fk_pago_detalle_cargo FOREIGN KEY (cargo_id) REFERENCES cargos(id),
    INDEX idx_pago_detalle_pago (pago_id),
    INDEX idx_pago_detalle_cargo (cargo_id)
);

CREATE TABLE faenas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    anio SMALLINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NULL,
    hora_fin TIME NULL,
    lugar VARCHAR(180),
    genera_cargo_inasistencia BOOLEAN NOT NULL DEFAULT FALSE,
    monto_inasistencia_default DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    concepto_cargo_id BIGINT UNSIGNED NULL,
    estatus ENUM('PROGRAMADA','EN_CURSO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PROGRAMADA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_faenas_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_faenas_concepto FOREIGN KEY (concepto_cargo_id) REFERENCES conceptos_cargo(id),
    INDEX idx_faenas_comunidad (comunidad_id),
    INDEX idx_faenas_anio (anio),
    INDEX idx_faenas_fecha (fecha)
);

CREATE TABLE faena_participantes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faena_id BIGINT UNSIGNED NOT NULL,
    persona_id BIGINT UNSIGNED NOT NULL,
    estatus ENUM('PENDIENTE','ASISTIO','NO_ASISTIO','JUSTIFICADO','EXENTO') NOT NULL DEFAULT 'PENDIENTE',
    monto_inasistencia_personalizado DECIMAL(12,2) NULL,
    hora_registro DATETIME NULL,
    observaciones TEXT,
    cargo_generado_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_faena_participantes_faena FOREIGN KEY (faena_id) REFERENCES faenas(id) ON DELETE CASCADE,
    CONSTRAINT fk_faena_participantes_persona FOREIGN KEY (persona_id) REFERENCES personas(id),
    CONSTRAINT fk_faena_participantes_cargo FOREIGN KEY (cargo_generado_id) REFERENCES cargos(id),
    UNIQUE KEY uk_faena_persona (faena_id, persona_id),
    INDEX idx_faena_participantes_estatus (estatus)
);

CREATE TABLE reuniones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    anio SMALLINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NULL,
    hora_fin TIME NULL,
    lugar VARCHAR(180),
    genera_cargo_inasistencia BOOLEAN NOT NULL DEFAULT FALSE,
    monto_inasistencia_default DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    concepto_cargo_id BIGINT UNSIGNED NULL,
    estatus ENUM('PROGRAMADA','EN_CURSO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PROGRAMADA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reuniones_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_reuniones_concepto FOREIGN KEY (concepto_cargo_id) REFERENCES conceptos_cargo(id),
    INDEX idx_reuniones_comunidad (comunidad_id),
    INDEX idx_reuniones_anio (anio),
    INDEX idx_reuniones_fecha (fecha)
);

CREATE TABLE reunion_participantes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reunion_id BIGINT UNSIGNED NOT NULL,
    persona_id BIGINT UNSIGNED NOT NULL,
    estatus ENUM('PENDIENTE','ASISTIO','NO_ASISTIO','JUSTIFICADO','EXENTO') NOT NULL DEFAULT 'PENDIENTE',
    monto_inasistencia_personalizado DECIMAL(12,2) NULL,
    hora_registro DATETIME NULL,
    observaciones TEXT,
    cargo_generado_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reunion_participantes_reunion FOREIGN KEY (reunion_id) REFERENCES reuniones(id) ON DELETE CASCADE,
    CONSTRAINT fk_reunion_participantes_persona FOREIGN KEY (persona_id) REFERENCES personas(id),
    CONSTRAINT fk_reunion_participantes_cargo FOREIGN KEY (cargo_generado_id) REFERENCES cargos(id),
    UNIQUE KEY uk_reunion_persona (reunion_id, persona_id),
    INDEX idx_reunion_participantes_estatus (estatus)
);

CREATE TABLE persona_obligaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    persona_id BIGINT UNSIGNED NOT NULL,
    concepto_id BIGINT UNSIGNED NOT NULL,
    tipo_estado ENUM('OBLIGATORIO','EXENTO','CUOTA_SUSTITUTIVA') NOT NULL DEFAULT 'OBLIGATORIO',
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    monto_personalizado DECIMAL(12,2) NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_persona_obligaciones_persona FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
    CONSTRAINT fk_persona_obligaciones_concepto FOREIGN KEY (concepto_id) REFERENCES conceptos_cargo(id),
    UNIQUE KEY uk_persona_obligacion (persona_id, concepto_id)
);

CREATE TABLE auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunidad_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NULL,
    accion VARCHAR(80) NOT NULL,
    entidad VARCHAR(80) NOT NULL,
    entidad_id BIGINT UNSIGNED NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_comunidad FOREIGN KEY (comunidad_id) REFERENCES comunidades(id),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_auditoria_comunidad (comunidad_id),
    INDEX idx_auditoria_usuario (usuario_id),
    INDEX idx_auditoria_entidad (entidad, entidad_id),
    INDEX idx_auditoria_fecha (created_at)
);

INSERT INTO comunidades (clave, nombre, municipio, estado)
VALUES ('DEMO001', 'Comunidad Demo', 'Ixmiquilpan', 'Hidalgo');

INSERT INTO conceptos_cargo
(comunidad_id, nombre, descripcion, tipo, monto_default, periodicidad)
VALUES
(1, 'Agua', 'Servicio comunitario de agua', 'SERVICIO', 100.00, 'MENSUAL'),
(1, 'Alumbrado', 'Aportación para alumbrado', 'SERVICIO', 50.00, 'MENSUAL'),
(1, 'Faena no realizada', 'Cargo generado por inasistencia a faena', 'FAENA', 300.00, 'EVENTUAL'),
(1, 'Inasistencia a reunión', 'Cargo opcional por inasistencia a reunión', 'REUNION', 0.00, 'EVENTUAL'),
(1, 'Cooperación anual', 'Cooperación general anual de la comunidad', 'COOPERACION_ANUAL', 0.00, 'ANUAL'),
(1, 'Cooperación de obra', 'Cooperación destinada a una obra comunitaria', 'COOPERACION_OBRA', 0.00, 'EVENTUAL');

CREATE OR REPLACE VIEW vw_estado_cuenta_persona AS
SELECT
    c.id AS cargo_id,
    c.comunidad_id,
    c.persona_id,
    p.numero_control,
    CONCAT_WS(' ', p.nombre, p.apellido_paterno, p.apellido_materno) AS persona,
    c.anio,
    cc.nombre AS concepto,
    cc.tipo AS tipo_concepto,
    o.nombre AS obra,
    c.descripcion,
    c.fecha_emision,
    c.fecha_vencimiento,
    c.monto,
    c.saldo,
    c.estatus
FROM cargos c
INNER JOIN personas p ON p.id = c.persona_id
INNER JOIN conceptos_cargo cc ON cc.id = c.concepto_id
LEFT JOIN obras o ON o.id = c.obra_id;

CREATE OR REPLACE VIEW vw_resumen_anual_persona AS
SELECT
    c.comunidad_id,
    c.persona_id,
    c.anio,
    SUM(c.monto) AS total_cargos,
    SUM(c.monto - c.saldo) AS total_pagado,
    SUM(c.saldo) AS total_pendiente
FROM cargos c
WHERE c.estatus NOT IN ('CANCELADO','CONDONADO')
GROUP BY c.comunidad_id, c.persona_id, c.anio;
