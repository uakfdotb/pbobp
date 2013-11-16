CREATE INDEX object_id ON pbobp_configuration(object_id);
CREATE INDEX object_type ON pbobp_configuration(object_type);
CREATE INDEX k ON pbobp_configuration(k);

CREATE INDEX email ON pbobp_users(email);

CREATE INDEX context ON pbobp_fields(context);
CREATE INDEX context_id ON pbobp_fields(context_id);
CREATE INDEX field_id ON pbobp_fields_options(field_id);
CREATE INDEX object_id ON pbobp_fields_values(object_id);
CREATE INDEX context ON pbobp_fields_values(context);
CREATE INDEX field_id ON pbobp_fields_values(field_id);

CREATE INDEX `primary` ON pbobp_currencies(`primary`);
CREATE INDEX iso_code ON pbobp_currencies(iso_code);
CREATE INDEX context ON pbobp_prices(context);
CREATE INDEX context_id ON pbobp_prices(context_id);

CREATE INDEX product_id ON pbobp_products_groups_members(product_id);
CREATE INDEX group_id ON pbobp_products_groups_members(group_id);
CREATE INDEX parent_id ON pbobp_products_addons(parent_id);
CREATE INDEX parent_type ON pbobp_products_addons(parent_type);
CREATE INDEX child_id ON pbobp_products_addons(child_id);

CREATE INDEX status ON pbobp_services(status);
CREATE INDEX product_id ON pbobp_services(product_id);
CREATE INDEX user_id ON pbobp_services(user_id);
CREATE INDEX parent_service ON pbobp_services(parent_service_id);

CREATE INDEX user_id ON pbobp_tickets(user_id);
CREATE INDEX department_id ON pbobp_tickets(department_id);
CREATE INDEX service_id ON pbobp_tickets(service_id);
CREATE INDEX status ON pbobp_tickets(status);
CREATE INDEX user_id ON pbobp_tickets_messages(user_id);
CREATE INDEX ticket_id ON pbobp_tickets_messages(ticket_id);

CREATE INDEX user_id ON pbobp_invoices(user_id);
CREATE INDEX `date` ON pbobp_invoices(`date`);
CREATE INDEX due_date ON pbobp_invoices(due_date);
CREATE INDEX status ON pbobp_invoices(status);
CREATE INDEX invoice_id ON pbobp_invoices_lines(invoice_id);

CREATE INDEX invoice_id ON pbobp_transactions(invoice_id);
CREATE INDEX user_id ON pbobp_transactions(user_id);

CREATE INDEX ip ON pbobp_locks(ip);
CREATE INDEX time ON pbobp_locks(time);

CREATE INDEX user_id ON pbobp_auth_tokens(user_id);
