CREATE TABLE tx_promoshowcase_domain_model_milestone (
    milestone_type varchar(32) DEFAULT '' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    milestone_date int(11) unsigned DEFAULT 0 NOT NULL,
    description text
);
