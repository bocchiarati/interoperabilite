<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:template match="/root">
        <xsl:choose>
            <xsl:when test="city = 'Nancy'">
                <p><strong>Pays</strong> : <xsl:value-of select="country_name"/> </p>
                <p><strong>Region</strong> : <xsl:value-of select="region"/></p>
                <p><strong>Ville</strong> : <xsl:value-of select="city"/></p>
                <p><strong>Code postal</strong> : <xsl:value-of select="postal"/></p>
            </xsl:when>
            <xsl:otherwise>
                <p><strong>Pays</strong> : France </p>
                <p><strong>Region</strong> : Grand-Est</p>
                <p><strong>Ville</strong> : Nancy (IUT Nancy Charlemagne)</p>
                <p><strong>Code postal</strong> : 54000 </p>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
