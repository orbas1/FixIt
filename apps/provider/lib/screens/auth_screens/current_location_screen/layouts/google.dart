import 'dart:convert';
import 'dart:developer';
import 'package:http/http.dart' as http;
import 'package:fixit_provider/screens/auth_screens/current_location_screen/layouts/location_list_tile.dart';

import '../../../../config.dart';

class SearchLocation extends StatefulWidget {
  const SearchLocation({super.key});

  @override
  State<SearchLocation> createState() => _SearchLocationState();
}

class _SearchLocationState extends State<SearchLocation> {
  List placePredictions = [];
  FocusNode focusNode = FocusNode();
  TextEditingController search = TextEditingController();
  String apiKey =
      "AIzaSyDPh-XpzRCLlQpQX9TlL3bb_KcJbYXggdo" /* "AIzaSyDNuJFHTBoAJeSsDdJhyuQrpkDo5_bl6As" */;

  placeAutoComplete(query) async {
    String api = "https://maps.googleapis.com/maps/api/place/autocomplete/json";
    String request =
        "$api?input=${search.text}&key=${appSettingModel!.firebase!.googleMapApiKey ?? api}";

    var res = await http.get(Uri.parse(request));

    var result = res.body.toString();

    if (res.statusCode == 200) {
      setState(() {
        placePredictions = jsonDecode(res.body.toString())['predictions'];
      });
    } else {
      log("EEERE :${res.body}");
    }
    setState(() {});
  }

  findCord(context, placeID) async {
    /*  log("message=-=-=-=-=-=-=-=-=-${appSettingModel!.firebase!.googleMapApiKey}"); */
    var d = await http.get(Uri.parse(
        "https://maps.googleapis.com/maps/api/place/details/json?place_id=$placeID&key=${appSettingModel!.firebase!.googleMapApiKey}"));
    var result = d.body.toString();
    dynamic a = jsonDecode(d.body);
    log("message=-=-=-=-=-=-=-=-=-${a['result']}");
    // log("`aa :${a['result']['geometry']}`");
    route.pop(context,
        arg: LatLng(a['result']['geometry']['location']['lat'],
            a['result']['geometry']['location']['lng']));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: AppBarCommon(title: language(context, translations!.location)),
        body: ListView(
          children: [
            TextFieldCommon(
                    border: OutlineInputBorder(
                        borderSide: BorderSide(
                            color: appColor(context).appTheme.stroke)),
                    focusNode: focusNode,
                    onChanged: (v) => placeAutoComplete(v),
                    controller: search,
                    hintText: language(context, translations!.searchHere),
                    prefixIcon: eSvgAssets.location)
                .paddingSymmetric(horizontal: Insets.i20),
            const VSpace(Sizes.s20),
            Divider(color: appColor(context).appTheme.stroke, height: 0),
            if (placePredictions.isNotEmpty) const VSpace(Sizes.s20),
            ButtonCommon(
                margin: 20,
                onTap: () => route.pop(context),
                title: language(context, translations!.useCurrentLocation),
                icon: SvgPicture.asset(
                  eSvgAssets.zipcode,
                  colorFilter: ColorFilter.mode(
                      appColor(context).appTheme.whiteBg, BlendMode.srcIn),
                )),
            const VSpace(Sizes.s20),
            Divider(color: appColor(context).appTheme.stroke, height: 0),
            if (placePredictions.isNotEmpty) const VSpace(Sizes.s20),
            ...placePredictions.asMap().entries.map((e) => LocationListTile(
                  loc: e.value['description'],
                  onTap: () {
                    log("message=-=-=-=-=-=-=-=-=-${e.value['place_id']}");
                    findCord(context, e.value['place_id']);
                  },
                )),
          ],
        ));
  }
}
