-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2025 a las 21:05:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `medicareconnect`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `video_call` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `video_call`) VALUES
(2, 1, 11, '2025-10-18', '09:40:00', 'cancelada', '2025-10-18 00:23:31', 1),
(4, 1, 11, '2025-11-10', '11:00:00', 'confirmada', '2025-10-19 07:33:42', 0),
(5, 1, 11, '2025-12-10', '11:00:00', 'confirmada', '2025-10-20 13:36:24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `office_address` varchar(255) DEFAULT NULL,
  `working_days` varchar(100) DEFAULT NULL,
  `working_hours` varchar(100) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization`, `office_address`, `working_days`, `working_hours`, `consultation_fee`, `profile_pic`) VALUES
(5, 4, 'Diagnostico por Imagen', 'Hospital Britanico', 'lunes, miercoles y viernes', '08:00-16:00', 50000.00, 'doctor_1756505363.jpg'),
(6, 5, 'Diagnostico por Imagen', 'Hospital Italiano', 'lunes, miercoles y viernes', '08:00-16:00', 80000.00, 'doctor_1756505524.jpg'),
(7, 6, 'Neurologo', 'San Salvador', 'lunes y miercoles', '08:00-13:00', 60000.00, 'doctor_1756505672.jpg'),
(8, 7, 'Ginecologo', 'Hospital Aleman', 'miercoles y viernes', '08:00-13:00', 100000.00, 'doctor_1756506026.jpeg'),
(9, 8, 'Urologo', 'Anchorena', 'lunes y miercoles', '08:00-16:00', 70000.00, 'doctor_1756506944.jpeg'),
(10, 9, 'Fisioteraputa', 'Hospital San jose', 'lunes, miercoles y viernes', '08:00-13:00', 90000.00, 'doctor_1756507834.jpeg'),
(11, 11, 'Medicina General', 'Calle Falsa 123', 'Lunes-Miercoles-Viernes', '09:00-17:00', 100000.00, 'doctor_11_logo.png'),
(12, 13, 'Pediatra', 'Obelisco', 'Lunes-Martes-Viernes', '09:00-13:00', 60000.00, 'doc_68f63a25a9b91_anto.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medical_notes`
--

CREATE TABLE `medical_notes` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `sender` enum('doctor','patient') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `read_status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `created_at`, `read_status`) VALUES
(1, 12, 'Actualización de turno', 'Tu turno fue confirmado ✅. Ya puedes acceder al chat y la videollamada desde tu panel.', '2025-10-20 13:57:30', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `birthdate`, `address`, `phone`, `blood_type`, `allergies`, `medical_conditions`, `emergency_contact_name`, `emergency_contact_phone`) VALUES
(1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `patient_studies`
--

CREATE TABLE `patient_studies` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `patient_studies`
--

INSERT INTO `patient_studies` (`id`, `patient_id`, `file_name`, `uploaded_at`) VALUES
(2, 12, '1760684838_logo.png', '2025-10-17 04:07:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `studies`
--

CREATE TABLE `studies` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `doctor_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `studies`
--

INSERT INTO `studies` (`id`, `patient_id`, `doctor_id`, `file_name`, `uploaded_at`, `doctor_comment`) VALUES
(5, 1, NULL, 'study_68f49028a219b_12_INVITACION.jpg', '2025-10-19 07:15:52', 'Dolor de cabeza'),
(6, 1, NULL, 'study_68f63aeca0bde_12_study_68f49028a219b_12_INVITACION.jpg', '2025-10-20 13:36:44', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `study_replies`
--

CREATE TABLE `study_replies` (
  `id` int(11) NOT NULL,
  `study_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `replied_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `typing_status`
--

CREATE TABLE `typing_status` (
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_typing` tinyint(1) DEFAULT 0,
  `patient_typing` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','doctor','patient') DEFAULT 'patient',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `fullName`, `email`, `profile_pic`, `password`, `role`, `avatar`, `created_at`) VALUES
(2, 'Administrador MedicareConnect', 'admin@medicareconnect.site', NULL, '$2y$10$V6ga8sQhEjUoPjDcYrAsruBD/56UCfw9i76zayNUFR4KZTd4bSY7C', 'admin', NULL, '2025-08-07 16:37:33'),
(3, 'Eduardo Perez', 'luchoperez806@gmail.com', NULL, '$2y$10$.kFOeGDtd6qsjwdXu6Zy/OJ.hpzp5gdg0D1kzDhJnlRZEl9Z3Iduu', 'patient', NULL, '2025-08-29 21:58:57'),
(4, 'Silvia Gimenez', 'gimenez.silvia@gmail.com', NULL, '$2y$10$tslHF8YmmYXb/LxFP.bNYefph1IjkYKrPHwsGtynKzTLM4a79GM7m', 'patient', NULL, '2025-08-29 22:09:23'),
(5, 'Mariangeles Gomez', 'marian@gmail.com', NULL, '$2y$10$2XrBW3tnqGyYtBWZ9NIMse9t8ZOj8vI0Ef86S1B9f7HtoyYjLcBLa', 'patient', NULL, '2025-08-29 22:12:04'),
(6, 'Jose Jutierrez', 'jose@gmail.com', NULL, '$2y$10$1E5R6.DsDdygrIjcwrjTheewqfmr4/Kjhe1kvpfxKmsK6CJfAj5dS', 'patient', NULL, '2025-08-29 22:14:32'),
(7, 'Guido Vietri', 'guido@gmail.com', NULL, '$2y$10$J8urXI1cqZnL22D3OPcYZeo44bGKmub5Q0y9iAtfJGsuNzOZBVOSa', 'patient', NULL, '2025-08-29 22:20:26'),
(8, 'Patricio Rosas', 'pato@gmail.com', NULL, '$2y$10$NCUqQ6eGTrAifxo3.b4OZOcRdcGqvyrBGTD4viX6pCo0CR5oGUI4W', 'patient', NULL, '2025-08-29 22:35:44'),
(9, 'Juan José Sardi', 'sardi@gmail.com', NULL, '$2y$10$Nk.jr.R7FWV/NEBawYf5oO1JwW1/24nP.7jaiGBjFQVxEvl3wbc4m', 'patient', NULL, '2025-08-29 22:50:34'),
(11, 'Dr. Prueba', 'dr.prueba@medicareconnect.site', NULL, '$2y$10$4YV1YqMerGcq88csTvECIOjI3phoWgL1AfYLg.MIop2q7AGt3D/tm', 'doctor', NULL, '2025-09-20 05:47:19'),
(12, 'Paciente Prueba', 'paciente.prueba@medicareconnect.site', NULL, '$2y$10$jik8A19MWWKzr.4TOozpy.FS9LrZBYueO5PwfghJ055wqgzKenYW6', 'patient', NULL, '2025-09-20 05:47:19'),
(13, 'Antonela Gomez', 'anto@gmail.com', 'doc_68f63a25a9b91_anto.jpg', '$2y$10$5roWJ.mSUndfVn0zHj11qOl4Uf4CEvno6nupW7AcHaGTrIB1sjQKe', 'doctor', NULL, '2025-10-20 13:33:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `code` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `patient_id`, `code`, `created_at`) VALUES
(1, 1, '84680f162529fe9f37993a48f71400ae', '2025-10-26 07:58:37');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indices de la tabla `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `medical_notes`
--
ALTER TABLE `medical_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indices de la tabla `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `patient_studies`
--
ALTER TABLE `patient_studies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indices de la tabla `studies`
--
ALTER TABLE `studies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indices de la tabla `study_replies`
--
ALTER TABLE `study_replies`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `typing_status`
--
ALTER TABLE `typing_status`
  ADD PRIMARY KEY (`doctor_id`,`patient_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `medical_notes`
--
ALTER TABLE `medical_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `patient_studies`
--
ALTER TABLE `patient_studies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `studies`
--
ALTER TABLE `studies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `study_replies`
--
ALTER TABLE `study_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Filtros para la tabla `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `medical_notes`
--
ALTER TABLE `medical_notes`
  ADD CONSTRAINT `medical_notes_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_notes_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `patient_studies`
--
ALTER TABLE `patient_studies`
  ADD CONSTRAINT `patient_studies_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`user_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `studies`
--
ALTER TABLE `studies`
  ADD CONSTRAINT `studies_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `studies_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
