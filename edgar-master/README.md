# edgar
PHP library for working with the SEC's `Edgar` data archive

# Load the US-GAAP Details Schema

$gaapDetails = \Edgar\Edgar::gaap();

# Load the US-GAAP Lables Schema

$gaapLabels = \Edgar\Edgar::gaapLabels();

# Load the Full US-GAAP Summary Schema

$gaapCollection = new \Edgar\Collections\GaapCollection();
$gaapSummary = $gaapCollection->summary();

# Load the DEI Details Schema

$deiDetails = \Edgar\Edgar::dei();

# Load the DEI Labels Schema

$deiLabels = \Edgar\Edgar::deiLabels();


