import 'package:fixit_user/config.dart';

class AdditionalServices {
  int? id;
  String? title;
  double? price;
  int? status;
  int? userId;
  String? parentId;
  List<int>? reviewRatings;
  int? ratingCount;
  List<CategoryModel>? categories;
  List<Media>? media;
  List<Reviews>? reviews;

  AdditionalServices(
      {this.id,
      this.title,
      this.price,
      this.status,
      this.userId,
      this.parentId,
      this.reviewRatings,
      this.ratingCount,
      this.categories,
      this.media,
      this.reviews});

  AdditionalServices.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    title = json['title'];
    price =
        json['price'] != null ? double.parse(json['price'].toString()) : null;
    status = json['status'];
    userId = json['user_id'];
    parentId = json['parent_id']?.toString();
    reviewRatings = json['review_ratings'] /* .cast<int>() */;
    ratingCount = json['rating_count'];
    if (json['categories'] != null) {
      categories = <CategoryModel>[];
      json['categories'].forEach((v) {
        categories!.add(CategoryModel.fromJson(v));
      });
    }
    if (json['media'] != null) {
      media = <Media>[];
      json['media'].forEach((v) {
        media!.add(Media.fromJson(v));
      });
    }
    if (json['reviews'] != null) {
      reviews = <Reviews>[];
      json['reviews'].forEach((v) {
        reviews!.add(Reviews.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['title'] = title;
    data['price'] = price;
    data['status'] = status;
    data['user_id'] = userId;
    data['parent_id'] = parentId;
    data['review_ratings'] = reviewRatings;
    data['rating_count'] = ratingCount;
    if (categories != null) {
      data['categories'] = categories!.map((v) => v.toJson()).toList();
    }
    if (media != null) {
      data['media'] = media!.map((v) => v.toJson()).toList();
    }
    if (reviews != null) {
      data['reviews'] = reviews!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}
