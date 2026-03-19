<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Shared
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */


/**
 * PHPExcel_Shared_PasswordHasher
 *
 * @category   PHPExcel
 * @package    PHPExcel_Shared
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Shared_PasswordHasher
{
    /**
     * Create a password hash from a given string.
     *
     * This method is based on the algorithm provided by
     * Daniel Rentz of OpenOffice and the PEAR package
     * Spreadsheet_Excel_Writer by Xavier Noguer <xnoguer@rezebra.com>.
     *
     * @param     string    $pPassword    Password to hash
     * @return     string                Hashed password
     */
    public static function hashPassword($pPassword = '')
    {
        $password   = 0x0000;
        $charPos    = 1;       // char position

        // split the plain text password in its component characters
        $chars = preg_split('//', $pPassword, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($chars as $char) {
            // Emulate 32-bit signed integer arithmetic to match Excel/32-bit PHP behaviour.
            // Shifts >= 32 bits produce 0 (undefined in C, conventionally 0 on most platforms).
            if ($charPos < 32) {
                $value = self::toInt32(ord($char) << $charPos);
            } else {
                $value = 0;
            }
            $charPos++;
            $rotated_bits    = ($value >> 15) & 0x1FFFF;   // rotated bits beyond bit 15
            $value            &= 0x7fff;                    // first 15 bits
            $password        = self::toInt32($password ^ ($value | $rotated_bits));
        }

        $password = self::toInt32($password ^ strlen($pPassword));
        $password = self::toInt32($password ^ 0xCE4B);

        // Convert to unsigned hex, matching 32-bit PHP dechex() behaviour for negative values
        $unsigned = ($password < 0) ? $password + 0x100000000 : $password;
        return strtoupper(dechex((int) $unsigned));
    }

    /**
     * Truncate a value to a signed 32-bit integer, matching 32-bit PHP/C behaviour.
     */
    private static function toInt32($n)
    {
        $n = (int) ($n & 0xFFFFFFFF);
        if ($n >= 0x80000000) {
            $n -= 0x100000000;
        }
        return $n;
    }
}
