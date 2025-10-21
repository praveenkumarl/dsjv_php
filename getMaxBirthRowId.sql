DELIMITER $$
CREATE DEFINER=`u835731528_dsjv`@`127.0.0.1` FUNCTION `getMaxBirthRowId`(`in_phone` VARCHAR(20) CHARSET utf8mb4) RETURNS int(11)
BEGIN
declare out_rowid INT;

select max(rowid) into out_rowid from birth_chart where phone=in_phone;

return out_rowid;

END$$
DELIMITER ;