-- #1120 update rx_setup for dosespot from 'Electronic' to 'Electronic_Dosespot'
UPDATE practice_settings SET rx_setup = 'Electronic_Dosespot' WHERE rx_setup = 'Electronic';
