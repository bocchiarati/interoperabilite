<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:template match="/root">
    <xsl:if test="city = 'Nancy'"><xsl:value-of select="latitude"/>,<xsl:value-of select="longitude"/></xsl:if>
    </xsl:template>
</xsl:stylesheet>
