<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" indent="yes" encoding="UTF-8"/>

    <xsl:template match="/">
            <div id="matin">
                <xsl:apply-templates select="previsions/echeance[@hour='9']">
                    <xsl:with-param name="label">Matin</xsl:with-param>
                </xsl:apply-templates>
            </div>

            <div id="midi">
                <xsl:apply-templates select="previsions/echeance[@hour='15']">
                    <xsl:with-param name="label">Après-midi</xsl:with-param>
                </xsl:apply-templates>
            </div>

            <div id="soir">
                <xsl:apply-templates select="previsions/echeance[@hour='21']">
                    <xsl:with-param name="label">Soir</xsl:with-param>
                </xsl:apply-templates>
            </div>
    </xsl:template>

    <xsl:template match="echeance">
        <xsl:param name="label"/>
        <h1><xsl:value-of select="$label"/></h1>
        <ul>
            <xsl:variable name="temp" select="round(number(temperature/level[@val='sol']) - 273.15)"/>
            <li>Température : <xsl:value-of select="$temp"/> °C
                <xsl:choose>
                    <xsl:when test="$temp &lt;= 5">🥶</xsl:when>
                    <xsl:when test="$temp &gt;= 25">🥵</xsl:when>
                    <xsl:otherwise>😐</xsl:otherwise>
                </xsl:choose>
            </li>
            <li>Min : <xsl:value-of select="$temp - 2"/> °C / Max : <xsl:value-of select="$temp + 2"/> °C</li>

            <xsl:if test="pluie &gt; 0"><li>Pluie 🌧️</li></xsl:if>
            <xsl:if test="vent_moyen/level &gt; 0"><li>Vent 💨</li></xsl:if>
            <xsl:if test="risque_neige='oui'"><li>Neige ❄️</li></xsl:if>
        </ul>
    </xsl:template>

</xsl:stylesheet>