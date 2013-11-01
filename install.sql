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

-- object_type is blank if this is a global configuration
-- it is not blank for things like user-specific or product-specific settings
-- also not blank for plugin settings
CREATE TABLE pbobp_configuration (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, k VARCHAR(16) NOT NULL, v VARCHAR(128) NOT NULL, object_id INT NOT NULL, object_type VARCHAR(32) NOT NULL DEFAULT '');

CREATE TABLE pbobp_users (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, email VARCHAR(128) NOT NULL DEFAULT '' UNIQUE, password VARCHAR(128) NOT NULL DEFAULT '', credit FLOAT NOT NULL DEFAULT 0, `access` INT NOT NULL DEFAULT 0);

-- type is 0=text box; 1=text area; 2=checkbox; 3=dropdown (needs options); 4=radiobutton (needs options)
-- context may be "user", "product", "group", "plugin"
--  if user, then context_id=0; otherwise, context_id = related table id or plugin name
CREATE TABLE pbobp_fields (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, context VARCHAR(32) NOT NULL, context_id INT NOT NULL, name VARCHAR(128) NOT NULL, `default` VARCHAR(1024) NOT NULL DEFAULT '', description VARCHAR(1024) NOT NULL DEFAULT '', type INT NOT NULL DEFAULT 0, required INT NOT NULL DEFAULT 0, adminonly INT NOT NULL DEFAULT 0);
CREATE TABLE pbobp_fields_options (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, field_id INT NOT NULL, val VARCHAR(1024) NOT NULL);
-- context may be "user", "service"
-- meaning of object_id depends on the context (user id or service id)
CREATE TABLE pbobp_fields_values (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, object_id INT NOT NULL, context VARCHAR(32) NOT NULL, context_id INT NOT NULL, field_id INT NOT NULL, val VARCHAR(1024) NOT NULL DEFAULT '');

CREATE TABLE pbobp_currencies (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, iso_code VARCHAR(3) NOT NULL, prefix VARCHAR(32) NOT NULL, suffix VARCHAR(32) NOT NULL, `primary` INT NOT NULL DEFAULT 0, rate FLOAT NOT NULL DEFAULT 1);
-- duration is 1=monthly, 3=quarterly, 6=semi-annually, 12=yearly, 0=one-time
-- amount is setup fee for recurring products
CREATE TABLE pbobp_prices (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, context VARCHAR(32) NOT NULL, context_id INT NOT NULL, duration INT NOT NULL DEFAULT 0, amount FLOAT NOT NULL DEFAULT 0, recurring_amount FLOAT NOT NULL DEFAULT 0, currency_id INT NOT NULL);

CREATE TABLE pbobp_products (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(128) NOT NULL, description VARCHAR(1024) NOT NULL DEFAULT '', uniqueid VARCHAR(32) NOT NULL DEFAULT '', plugin_id INT, addon INT NOT NULL DEFAULT 0);
CREATE TABLE pbobp_products_groups (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64) NOT NULL, description VARCHAR(1024) NOT NULL DEFAULT '', hidden NOT NULL DEFAULT 0);
CREATE TABLE pbobp_products_groups_members (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, product_id INT NOT NULL, group_id INT NOT NULL);
-- parent_type is 0=product, 1=product group
CREATE TABLE pbobp_products_addons (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, parent_id INT NOT NULL, child_id INT NOT NULL, parent_type INT NOT NULL DEFAULT 0);

-- status is 0=not yet active, 1=active, -1=suspended, -2=inactivated
CREATE TABLE pbobp_services (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, product_id INT NOT NULL, name VARCHAR(64) NOT NULL, creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, recurring_date TIMESTAMP NOT NULL, recurring_duration INT NOT NULL DEFAULT 0, recurring_amount FLOAT NOT NULL DEFAULT 0, status INT NOT NULL DEFAULT 0, parent_service INT, currency_id INT);
CREATE TABLE pbobp_services_settings (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, service_id INT NOT NULL, field_id INT NOT NULL, val VARCHAR(1024) NOT NULL DEFAULT '');

-- status is 0=open, 1=closed, -1=flagged (forced open), -2=on hold (marked as replied)
CREATE TABLE pbobp_tickets (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, department_id INT NOT NULL, service_id INT NOT NULL, subject VARCHAR(128) NOT NULL DEFAULT '', email VARCHAR(128) NOT NULL DEFAULT '', time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, modify_time TIMESTAMP NOT NULL, status INT NOT NULL DEFAULT 0);
CREATE TABLE pbobp_tickets_messages (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, ticket_id INT NOT NULL, content TEXT NOT NULL DEFAULT '', email VARCHAR(128) NOT NULL DEFAULT '', time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE pbobp_tickets_departments (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64) NOT NULL);

-- status is 0=unpaid, 1=paid, 2=cancelled
CREATE TABLE pbobp_invoices (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, due_date TIMESTAMP NOT NULL, status INT NOT NULL DEFAULT 0, amount FLOAT NOT NULL DEFAULT 0, paid FLOAT NOT NULL DEFAULT 0, currency_id INT NOT NULL);
CREATE TABLE pbobp_invoices_lines (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, invoice_id INT NOT NULL, amount FLOAT NOT NULL DEFAULT 0, service_id INT NOT NULL, description VARCHAR(128) NOT NULL DEFAULT '');

CREATE TABLE pbobp_transactions (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, invoice_id INT NOT NULL, user_id INT NOT NULL, gateway_id INT NOT NULL, notes VARCHAR(128) NOT NULL DEFAULT '');

CREATE TABLE pbobp_plugins (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64) NOT NULL);

CREATE TABLE pbobp_locks (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(16) NOT NULL DEFAULT '', time INT NOT NULL DEFAULT 0, action VARCHAR(16) NOT NULL DEFAULT '', num INT NOT NULL DEFAULT 0);
