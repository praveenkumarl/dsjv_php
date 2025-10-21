CREATE FUNCTION load_user_expiry()
BEGIN

DECLARE done INT DEFAULT 0;
DECLARE v_phone VARCHAR(20);
DECLARE v_imei VARCHAR(48);
DECLARE v_registered_date DATE;
DECLARE v_end_date DATETIME;
DECLARE v_user_type varchar(20);

DECLARE cur CURSOR FOR SELECT phone, imei, user_type, registered_date FROM user;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

OPEN cur;

label: LOOP
	FETCH cur INTO v_phone, v_imei, v_user_type, v_registered_date;
	
	IF STRCMP(v_user_type,"FREE")=0 THEN 
		SET v_end_date = CONVERT_TZ(DATE_ADD(v_registered_date,INTERVAL 1 DAY),'+00:00','+05:30');
	ELSEIF STRCMP(v_user_type,"PAID")=0 THEN
		SET v_end_date = CONVERT_TZ(DATE_ADD(v_registered_date,INTERVAL 1 YEAR),'+00:00','+05:30');
	ELSEIF STRCMP(v_user_type,"SUPER")=0 THEN
		SET v_end_date = CONVERT_TZ(DATE_ADD(v_registered_date,INTERVAL 1 YEAR),'+00:00','+05:30');
	ELSEIF STRCMP(v_user_type,"ADMIN")=0 THEN
		SET v_end_date = CONVERT_TZ(DATE_ADD(v_registered_date,INTERVAL 10 YEAR),'+00:00','+05:30');
	ELSE
		SET v_end_date = CONVERT_TZ(DATE_ADD(v_registered_date,INTERVAL 1 DAY),'+00:00','+05:30');
	END IF;
	
	INSERT IGNORE INTO user_trial VALUES(v_phone, v_imei, v_end_date);
	
	IF done = 1 THEN 
		LEAVE label;
	END IF;
END LOOP;

CLOSE cur; 

RETURN true;

END

