CREATE TRIGGER `update_user_trial` AFTER UPDATE ON `user`
 FOR EACH ROW BEGIN

IF STRCMP(OLD.phone,NEW.phone)!=0 THEN 
	UPDATE user_trial SET phone = NEW.phone WHERE phone = OLD.phone;
	UPDATE birth_chart SET phone = NEW.phone WHERE phone = OLD.phone;
	UPDATE jama_chart SET phone = NEW.phone WHERE phone = OLD.phone;
	UPDATE matching_chart SET phone = NEW.phone WHERE phone = OLD.phone;
	UPDATE activity_log SET phone = NEW.phone WHERE phone = OLD.phone;
END IF;
                             
END