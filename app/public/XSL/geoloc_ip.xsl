<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:template match="/root">
        <xsl:choose>
            <xsl:when test="city = 'Nancy'"><xsl:value-of select="latitude"/>,<xsl:value-of select="longitude"/></xsl:when>
            <xsl:otherwise>48.68298,6.16095</xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
