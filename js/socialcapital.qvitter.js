// translations
var socialCapitalTranslations = {
    en: {
        socialCapitalTooltip: "Social Capital Index"
    },
    es: {
        socialCapitalTooltip: "Social Capital Index"
    },
    sv: {
        socialCapitalTooltip: "Socialt kapital-index"
    }
};

// add translation to Qvitter
window.pluginTranslations.push(socialCapitalTranslations);

// whenever a .profile-card is added to DOM, add social capital
$('body').on('DOMNodeInserted', function(e) {

    //  the inserted node can either be a profile card or contain one
    if($(e.target).is('.profile-card')) {
        var profileCard = $(e.target);
    } else if ($(e.target).find('.profile-card').length>0) {
        var profileCard = $(e.target).find('.profile-card');
    } else {
        return true;
    }

    // don't do anyting if we've already added social capital
    if(profileCard.find('.social-capital-number').length>0) {
        return true;
    }

    // profile cards has the user JSON stored in a script tag
    var userData = JSON.parse(profileCard.children('script.profile-json').text());

    // add the number to the profile card
    profileCard.children('.profile-header-inner').prepend('<div class="social-capital-number"><a data-tooltip="' + window.sL.socialCapitalTooltip + '" href="' + window.siteInstanceURL + 'socialcapital">' + userData.social_capital + '</a></div>');
});
