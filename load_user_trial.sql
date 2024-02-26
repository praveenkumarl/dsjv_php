CREATE PROCEDURE load_user_expiry()
BEGIN

DECLARE done INT DEFAULT 0;
DECLARE cur CURSOR FOR SELECT phone, imei, user_type, registered_date FROM user;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
DECLARE phone VARCHAR(20);
DECLARE imei VARCHAR(48);
DECLARE registered_date DATE;
DECLARE end_date DATETIME;
DECLARE user_type varchar(20)

OPEN cur;

label: LOOP
	FETCH cur INTO phone, imei, registered_date;
	
	IF STRCMP(user_type,"FREE")=0 THEN 
		SET end_date = CONVERT_TZ(DATE_ADD(registered_date,INTERVAL 1 DAY),'+00:00','+05:30');
	ELSEIF STRCMP(user_type,"PAID")=0 THEN
		SET end_date = CONVERT_TZ(DATE_ADD(registered_date,INTERVAL 1 YEAR),'+00:00','+05:30');
	ELSEIF STRCMP(user_type,"SUPER")=0 THEN
		SET end_date = CONVERT_TZ(DATE_ADD(registered_date,INTERVAL 1 YEAR),'+00:00','+05:30');
	ELSEIF STRCMP(user_type,"ADMIN")=0 THEN
		SET end_date = CONVERT_TZ(DATE_ADD(registered_date,INTERVAL 10 YEAR),'+00:00','+05:30');
	ELSE
		SET end_date = CONVERT_TZ(DATE_ADD(registered_date,INTERVAL 1 DAY),'+00:00','+05:30');
	END IF;
	
	INSERT IGNORE INTO user_trial VALUES(phone, imei, end_date);
	
	IF done = 1 THEN 
		LEAVE label;
	END IF;
END LOOP;

CLOSE cur; 

END

