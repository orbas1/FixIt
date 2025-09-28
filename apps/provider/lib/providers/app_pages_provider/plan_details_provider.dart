import 'dart:async';
import 'dart:developer';
import 'package:collection/collection.dart';
import 'package:dio/dio.dart';
import 'package:fixit_provider/config.dart';
import 'package:in_app_purchase/in_app_purchase.dart';

class PlanDetailsProvider with ChangeNotifier {
  bool isMonthly = false, isTrial = false;
  CarouselSliderController carouselController = CarouselSliderController();
  List<SubscriptionModel> planList = [];
  int selIndex = 0;
  dynamic price;

  final InAppPurchase iap = InAppPurchase.instance;
  bool _isAvailable = false;
  List<ProductDetails> products = [];
  bool isLoading = true;
  String? productPrice;
  String subscriptionStatus = 'Not Subscribed';
  Map<String, String> priceMap = {};
  Map<String, dynamic> subscriptionDetailsMap = {}; // Store subscription data
  StreamSubscription<List<PurchaseDetails>>? _purchaseStreamSubscription;

  // Getter for subscription details
  Map<String, dynamic> get subscriptionDetails => subscriptionDetailsMap;

  @override
  void dispose() {
    _purchaseStreamSubscription?.cancel();
    super.dispose();
  }

  Future<void> initializeIAP(BuildContext context) async {
    try {
      log("Initializing IAP...");
      final bool available = await iap.isAvailable();
      if (!context.mounted) return;

      _isAvailable = available;
      isLoading = true;
      notifyListeners();

      if (!available) {
        log("In-app purchase is not available");
        _showSnackBar(context, 'In-app purchase is not available');
        isLoading = false;
        notifyListeners();
        return;
      }

      final ProductDetailsResponse response = await iap.queryProductDetails(
          subscriptionIds.map((id) => id.toString()).toSet());
      log("Fetched Products: ${response.productDetails.length} items");
      if (response.notFoundIDs.isNotEmpty) {
        log("Not found product IDs: ${response.notFoundIDs}");
      }

      if (!context.mounted) return;

      products = response.productDetails;
      priceMap = {for (var p in products) p.id: p.price};
      log("Price map: $priceMap");
      isLoading = false;
      notifyListeners();

      _purchaseStreamSubscription?.cancel();
      _purchaseStreamSubscription = iap.purchaseStream.listen(
        (purchaseDetailsList) {
          log("Purchase stream event received: ${purchaseDetailsList.length} items");
          _listenToPurchaseUpdated(context, purchaseDetailsList);
        },
        onError: (error) {
          log("Purchase stream error: $error");
          _showSnackBar(context, 'Error in purchase stream');
        },
        onDone: () {
          log("Purchase stream closed");
        },
      );
    } catch (e) {
      log("Error initializing IAP: $e");
      _showSnackBar(context, 'Failed to initialize subscriptions');
      notifyListeners();
    }
  }

  void _showSnackBar(BuildContext context, String message,
      {Color? color, Color? textColor}) {
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          backgroundColor: color,
          content: Text(
            message,
            style: TextStyle(color: textColor ?? Colors.black),
          )));
    }
  }

  final dioo = Dio();

  Future<void> _listenToPurchaseUpdated(
      BuildContext context, List<PurchaseDetails> purchaseDetailsList) async {
    log("Processing ${purchaseDetailsList.length} purchase updates");
    for (var purchaseDetails in purchaseDetailsList) {
      log("Purchase details: ID=${purchaseDetails.productID}, Status=${purchaseDetails.status}");
      switch (purchaseDetails.status) {
        case PurchaseStatus.pending:
          log("Purchase pending for ${purchaseDetails.productID}");
          _showSnackBar(context, 'Purchase is pending...');
          break;
        case PurchaseStatus.purchased:
          // Log all relevant data
          log("Purchase successful for ${purchaseDetails.productID}");
          subscriptionStatus = 'Subscribed to ${purchaseDetails.productID}';
          final rawPrice = priceMap[purchaseDetails.productID] ?? '0.0';
          final cleanedPrice =
              rawPrice.replaceAll(RegExp(r'[^0-9.]'), ''); // Remove commas
          final doublePrice = double.tryParse(cleanedPrice) ?? 0.0;
          var body = {
            "plan_id": planList[selIndex].id,
            "product_id": purchaseDetails.productID /*userSubscribe!.id*/,
            "in_app_status": purchaseDetails.status.toString(),
            'source': purchaseDetails.verificationData.source,
            'in_app_price': doublePrice,
          };
          log("BODY:::$body");
          await apiServices
              .postApi(api.subscriptionPlanCreate, body,
                  isToken: true, isData: true)
              .then((value) {
            if (value.isSuccess!) {
              _showSnackBar(context, value.message,
                  color: appColor(context).appTheme.green);
            } else {
              _showSnackBar(context, value.message, color: Colors.red);
              log("value.message::${value.message}");
            }
            log("DDDDD:${value.isSuccess}");
            hideLoading(context);
          });

          subscriptionDetailsMap = {
            'productID': purchaseDetails.productID,
            'status': purchaseDetails.status.toString(),
            'transactionDate': purchaseDetails.transactionDate ?? 'N/A',
            'purchaseID': purchaseDetails.purchaseID ?? 'N/A',
            'serverVerificationData':
                purchaseDetails.verificationData.serverVerificationData,
            'localVerificationData':
                purchaseDetails.verificationData.localVerificationData,
            'source': purchaseDetails.verificationData.source,
            'price': priceMap[purchaseDetails.productID] ?? 'N/A',
          };
          log("Subscription details: $subscriptionDetailsMap");
          _showSnackBar(context,
              'Subscribed to ${purchaseDetails.productID} for ${priceMap[purchaseDetails.productID]}!');
          if (purchaseDetails.pendingCompletePurchase) {
            iap.completePurchase(purchaseDetails);
            log("Purchase completed for ${purchaseDetails.productID}");
          }
          notifyListeners();
          break;
        case PurchaseStatus.restored:
          log("Purchase restored for ${purchaseDetails.productID}");
          subscriptionStatus =
              'Restored subscription to ${purchaseDetails.productID}';
          subscriptionDetailsMap = {
            'productID': purchaseDetails.productID,
            'status': purchaseDetails.status.toString(),
            'transactionDate': purchaseDetails.transactionDate ?? 'N/A',
            'purchaseID': purchaseDetails.purchaseID ?? 'N/A',
            'serverVerificationData':
                purchaseDetails.verificationData.serverVerificationData,
            'localVerificationData':
                purchaseDetails.verificationData.localVerificationData,
            'source': purchaseDetails.verificationData.source,
            'price': priceMap[purchaseDetails.productID] ?? 'N/A',
          };
          log("Restored subscription details: $subscriptionDetailsMap");
          _showSnackBar(context,
              'Restored subscription to ${purchaseDetails.productID}!');
          if (purchaseDetails.pendingCompletePurchase) {
            iap.completePurchase(purchaseDetails);
            log("Purchase completed for ${purchaseDetails.productID}");
          }
          notifyListeners();
          break;
        case PurchaseStatus.error:
          log("Purchase failed for ${purchaseDetails.productID}: ${purchaseDetails.error?.message}");
          _showSnackBar(context,
              'Purchase failed: ${purchaseDetails.error?.message ?? "Unknown error"}');
          break;
        case PurchaseStatus.canceled:
          log("Purchase canceled for ${purchaseDetails.productID}");
          _showSnackBar(context, 'Purchase was canceled');
          break;
      }
    }
  }

  Future<void> buySubscription(
      ProductDetails product, BuildContext context) async {
    try {
      log("Initiating purchase for: ${product.id}");

      final PurchaseParam purchaseParam =
          PurchaseParam(productDetails: product);
      final bool success =
          await iap.buyNonConsumable(purchaseParam: purchaseParam);

      log("Purchase initiation result: $success");
      if (!success) {
        _showSnackBar(context, 'Failed to initiate purchase for ${product.id}');
      } else {}
    } catch (e) {
      log("Error initiating purchase for ${product.id}: $e");
      _showSnackBar(context, 'Failed to initiate purchase: $e');
    }
  }

  void onPageChange(int index, dynamic reason) {
    selIndex = index;
    notifyListeners();
  }

  Future<void> onReady(BuildContext context) async {
    try {
      dynamic arg = ModalRoute.of(context)?.settings.arguments;
      isTrial = arg != null && arg['isTrial'] == true;
      log("onReady: isTrial=$isTrial, isMonthly=$isMonthly");
      await initializeIAP(context);

      if (subscriptionList.isNotEmpty) {
        planList = subscriptionList.where((element) {
          log("Checking plan: ${element.id}, duration=${element.duration}");
          return element.duration == (isMonthly ? "monthly" : "yearly");
        }).toList();
      } else {
        showLoading(context);
        final commonApi = Provider.of<CommonApiProvider>(context, listen: false)
            .getSubscriptionPlanList(context);

        planList = subscriptionList.where((element) {
          log("Checking plan: ${element.id}, duration=${element.duration}");
          return element.duration == (isMonthly ? "monthly" : "yearly");
        }).toList();
        hideLoading(context);
      }

      if (userModel?.activeSubscription != null) {
        int index = subscriptionList.indexWhere((element) =>
            element.id.toString() ==
            userModel?.activeSubscription?.userPlanId.toString());
        if (index >= 0) {
          isMonthly = subscriptionList[index].duration == "monthly";
          selIndex = planList.indexWhere(
              (element) => element.id == subscriptionList[index].id);
          selIndex = selIndex >= 0 ? selIndex : 0;
          log("Active subscription found: index=$selIndex, isMonthly=$isMonthly");
        }
      } else {
        selIndex = 0;
      }

      log("Plan list: ${planList.map((e) => e.id).toList()}, selIndex=$selIndex");
      // Defer jumpToPage until after the frame is built
      WidgetsBinding.instance.addPostFrameCallback((_) {
        carouselController.jumpToPage(selIndex);
      });
      notifyListeners();
    } catch (e) {
      log("Error in onReady: $e");
      _showSnackBar(context, 'Failed to load plans');
    }
  }

  void onToggle(bool val) {
    log("Toggling isMonthly to: $val");
    isMonthly = val;
    planList = subscriptionList.where((element) {
      return element.duration == (isMonthly ? "monthly" : "yearly");
    }).toList();
    selIndex = 0;
    carouselController.jumpToPage(selIndex);
    log("Updated planList: ${planList.map((e) => e.id).toList()}");
    notifyListeners();
  }

  Future<void> selectPlanByProduct(
      ProductDetails product, BuildContext context) async {
    try {
      log("Selecting plan for product: ${product.id}");
      bool isMonthlyProduct = product.id.contains('month');
      if (isMonthly != isMonthlyProduct) {
        log("Mismatch: isMonthly=$isMonthly, product=${product.id}");
        _showSnackBar(context,
            'Please select a ${isMonthly ? "monthly" : "yearly"} plan');
        return;
      }

      await buySubscription(product, context);

      final plan = getPlanById(product.id);
      if (plan != null) {
        await selectPlan(plan, context);
      } else {
        log("No matching plan found for ${product.id}");
      }
    } catch (e) {
      log("Error selecting plan by product: $e");
      _showSnackBar(context, 'Failed to select plan');
    }
  }

  SubscriptionModel? getPlanById(String id) {
    return planList.firstWhereOrNull((p) => p.id == id);
  }

  Future<void> selectPlan(SubscriptionModel plan, BuildContext context) async {
    try {
      log("Selecting plan: ${plan.id}");
      userSubscribe = plan;

      final commonApi = Provider.of<CommonApiProvider>(context, listen: false);
      await commonApi.getPaymentMethodList();
      notifyListeners();

      if (context.mounted) {
        route.pushNamed(context, routeName.paymentMethodList, arg: isTrial);
      }
    } catch (e) {
      log("Error selecting plan: $e");
      _showSnackBar(context, 'Failed to proceed to payment');
    }
  }
}
