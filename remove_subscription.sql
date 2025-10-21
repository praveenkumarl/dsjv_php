DELIMITER $$
CREATE PROCEDURE `remove_subscription`()
BEGIN
	DECLARE v_phone VARCHAR(20);
	DECLARE v_expiry_date DATE;
	DECLARE finished INTEGER DEFAULT 0;

-- declare cursor for employee email
	DEClARE cursor_subscription_user 
		CURSOR FOR 
			SELECT phone, expiry_date FROM subscription;

	-- declare NOT FOUND handler
	DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

	OPEN cursor_subscription_user;
	
	subscription_user: LOOP
		FETCH cursor_subscription_user INTO v_phone, v_expiry_date;

		IF finished = 1 THEN 
			LEAVE subscription_user;
		END IF;
		
				select v_phone, v_expiry_date;
		
		-- Check expiry
		
		IF DATE(NOW()) > v_expiry_date THEN
			UPDATE user SET user_type = 'FREE' WHERE phone = v_phone;
		END IF;
	
	END LOOP subscription_user;
	CLOSE cursor_subscription_user;

END$$
DELIMITER ;