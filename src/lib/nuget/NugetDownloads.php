<?php



class NugetDownloads
{
    /**
     * Bump the download counters for a package on MySQL: the per-version
     * counter (Id + Version) and the per-Id total. No-op on non-mysql backends.
     *
     * @param string $id
     * @param string $version
     * @return void
     */
    public function incrementDownloads($id, $version)
    {
        $properties = GlobalRegistry::get("properties");
        if ($properties->getProperty("dbtype", "file") != "mysql") {
            return;
        }

        /** @var \mysqli $mysqli */
        $mysqli = GlobalRegistry::get("mysqli");

        $stmt = $mysqli->prepare(
            "UPDATE `packages` SET `VersionDownloadCount` = `VersionDownloadCount` + 1 WHERE `Id` = ? AND `Version` = ?"
        );
        $stmt->bind_param("ss", $id, $version);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare(
            "UPDATE `packages` SET `DownloadCount` = `DownloadCount` + 1 WHERE `Id` = ?"
        );
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
    }
}
