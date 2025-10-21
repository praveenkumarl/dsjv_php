DELIMITER $$
CREATE PROCEDURE `remove_trial`()
BEGIN
	DECLARE v_phone VARCHAR(20);
	DECLARE v_expiry_date DATE;
	DECLARE finished INTEGER DEFAULT 0;

-- declare cursor for employee email
	DEClARE cursor_trial_user 
		CURSOR FOR 
			SELECT phone, expiry_date FROM user_trial;

	-- declare NOT FOUND handler
	DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

	OPEN cursor_trial_user;
	
	trial_user: LOOP
		FETCH cursor_trial_user INTO v_phone, v_expiry_date;

		IF finished = 1 THEN 
			LEAVE trial_user;
		END IF;
		
				select v_phone, v_expiry_date;
		
		-- Check expiry
		
		IF DATE(NOW()) > v_expiry_date THEN
			UPDATE user SET user_type = 'FREE' WHERE phone = v_phone;
		END IF;
	
	END LOOP trial_user;
	CLOSE cursor_trial_user;

END$$
DELIMITER ;