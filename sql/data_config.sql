--
--	pbobp
--	Copyright [2013] [Favyen Bastani]
--
--	This file is part of the pbobp source code.
--
--	pbobp is free software: you can redistribute it and/or modify
--	it under the terms of the GNU General Public License as published by
--	the Free Software Foundation, either version 3 of the License, or
--	(at your option) any later version.
--
--	pbobp source code is distributed in the hope that it will be useful,
--	but WITHOUT ANY WARRANTY; without even the implied warranty of
--	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
--	GNU General Public License for more details.
--
--	You should have received a copy of the GNU General Public License
--	along with pbobp source code. If not, see <http://www.gnu.org/licenses/>.
--

INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('site_name', 'pbobp', 'The name of this pbobp site', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('ticket_content_maxlen', '20000', 'The maximum length of a ticket', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('invoice_pre_days', '7', 'Time in days to send an invoice before a service due date', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('login_auto_admin', '1', 'Whether to automatically login administrators to the administration area', 2);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_footer', 'Thanks,\npbobp', 'Footer for e-mail messages sent from pbobp', 1);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('auth_password_minlen', '6', 'Minimum length for passwords', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('auth_password_maxlen', '512', 'Maximum length for passwords (note that passwords may be hashed, and excessively long ones could pose a denial of service risk)', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('service_display_max', '50', 'Maximum number of services to display in a table', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('user_display_max', '50', 'Maximum number of users to display in a table', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('invoice_display_max', '50', 'Maximum number of invoices to display in a table', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('ticket_display_max', '50', 'Maximum number of tickets to display in a table', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('theme_name', 'bootstrap', 'pbobp theme to display with', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_from', 'noreply@example.com', 'E-mail address to send emails from', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_smtp', '0', 'Set to use SMTP to send emails (instead of PHP mail function)', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_smtp_host', 'localhost', 'SMTP hostname (prefix with ssl:// or tls:// to use SSL or TLS)', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_smtp_port', '25', 'SMTP port', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_smtp_username', '', 'SMTP username', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('mail_smtp_password', '', 'SMTP password', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('captcha_interface', 'default', 'Captcha interface to use (default for none)', 0);
INSERT INTO pbobp_configuration (k, v, description, type) VALUES ('service_activate_immediate', '1', 'Whether to activate services immediately upon payment', 2);
