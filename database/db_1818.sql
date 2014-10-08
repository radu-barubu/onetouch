ALTER TABLE practice_settings MODIFY rx_setup VARCHAR(30) ;
ALTER TABLE practice_settings MODIFY labs_setup VARCHAR(30) ;
UPDATE practice_settings SET rx_setup = 'Electronic_Dosespot' WHERE rx_setup = 'Electronic';
UPDATE practice_settings SET rx_setup = 'Electronic_Dosespot' WHERE rx_setup = 'Electronic_Dose';
