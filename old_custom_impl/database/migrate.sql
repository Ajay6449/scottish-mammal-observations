-- Database Migration: Add Provenance Fields to Observations Table
-- To support importing real biodiversity occurrences from GBIF/NBN Atlas

ALTER TABLE observations
ADD COLUMN observation_type ENUM('imported', 'user_submitted') NOT NULL DEFAULT 'user_submitted',
ADD COLUMN source_dataset VARCHAR(255) NULL,
ADD COLUMN source_record_id VARCHAR(100) NULL,
ADD COLUMN source_url VARCHAR(255) NULL,
ADD COLUMN licence VARCHAR(100) NULL,
ADD COLUMN data_provider VARCHAR(255) NULL;

-- Add indexes for optimization and duplicate prevention
ALTER TABLE observations
ADD INDEX idx_observation_type (observation_type),
ADD UNIQUE KEY uq_source_record (source_record_id);
